<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Form\Type\RecipeType;
use Ibtikar\GlanceDashboardBundle\Document\Blog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Document\Tag;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

class BlogController extends BackendController
{

    protected $translationDomain = 'recipe';


    /**
     * @author Ahmad Gamal <a.gamal@ibtikar.net.sa>
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {

        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Blog'),);
        $breadCrumbArray = $this->preparedMenu($menus);

        $recipe = new Recipe();
        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('contentType' => 'blog', 'type' => 'add', 'class' => 'dev-page-main-form dev-js-validation form-horizontal'),'type'=>'create'));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('recipe');

                if($formData['related_article']){
                    $this->updateRelatedRecipe($recipe, $formData['related_article'],$dm,'article');
                }

                if($formData['related_tip']){
                    $this->updateRelatedRecipe($recipe, $formData['related_tip'],$dm,'tip');
                }

                $tags = $formData['tags'];
                $tagsEn = $formData['tagsEn'];

                $recipe->setTags();
                $recipe->setTagsEn();

                if ($tags) {
                    $tagsArray = explode(',', $tags);
                    $tagsArray = array_unique($tagsArray);
                    foreach ($tagsArray as $tag) {
                        $tag = trim($tag);
                        if (mb_strlen($tag, 'UTF-8') <= 330) {
                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('tag' => $tag));
                               if (!$tagObject) {
                                $NewTag = new Tag();
                                $NewTag->setName($tag);
                                $NewTag->setTag($tag);
                                $dm->persist($NewTag);
                                $recipe->addTag($NewTag);
                            } else {
                                $recipe->addTag($tagObject);
                            }
                        }
                    }
                }
                if ($tagsEn) {
                    $tagsArray = explode(',', $tagsEn);
                    $tagsArray = array_unique($tagsArray);
                    foreach ($tagsArray as $tag) {
                        $tag = trim($tag);
                        if (mb_strlen($tag, 'UTF-8') <= 330) {
                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('tagEn' => $tag));
                            if (!$tagObject) {
                                $NewTag = new Tag();
                                $NewTag->setName($tag);
                                $NewTag->setTagEn($tag);
                                $dm->persist($NewTag);
                                $recipe->addTagEn($NewTag);
                            } else {
                                $recipe->addTagEn($tagObject);
                            }
                        }
                    }
                }
                $dm->persist($recipe);
                $this->slugifier($recipe);

                $dm->flush();

                $this->updateMaterialGallary($recipe, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_recipenew_list_new_recipe'), array(), true));

            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Blog:create.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'title' => $this->trans('Add new Blog', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }



    public function updateMaterialGallary($document, $gallary, $dm = null) {
        if (!$dm) {
            $dm = $this->get('doctrine_mongodb')->getManager();
        }

        $gallary = json_decode($gallary,true);

        if (isset($gallary[0]) && is_array($gallary[0])) {

                $imagesIds = array();
                $imagesData = array();
                foreach ($gallary as $galleryImageData) {
                    $imagesIds [] = $galleryImageData['id'];
                    $imagesData[$galleryImageData['id']] = $galleryImageData;
                }
                $images = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('id' => array('$in' => $imagesIds)));
                if (count($images) > 0 ) {
                    $firstImg = $images[0];
                    $this->oldDir = $firstImg->getUploadRootDir();
                    $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $document->getId();
                    if (!file_exists($newDir)) {
                        @mkdir($newDir);
                    }
                }
                $documentImages = 0;

                $documentImages = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('recipe' => $document->getId(), 'coverPhoto' => false));

                $count= count($documentImages);
                $coverExist = FALSE;

                foreach ($images as $mediaObj) {
                    $image = $imagesData[$mediaObj->getId()];

                    $oldFilePath = $this->oldDir . "/" . $mediaObj->getPath();
                    $newFilePath = $newDir . "/" . $mediaObj->getPath();
                    @rename($oldFilePath, $newFilePath);

                    $mediaObj->setRecipe($document);

                    $mediaObj->setOrder($image['order']);
                    $mediaObj->setCaptionAr($image['captionAr']);
                    $mediaObj->setCaptionEn($image['captionEn']);

                    $mediaObj->setCoverPhoto(FALSE);
                    //                set default cover photo in case it's from the gallary images
                    if (isset($image['cover']) && $image['cover']) {
                        $document->setDefaultCoverPhoto($mediaObj->getPath());
                        $document->setCoverPhoto($mediaObj);

                        $mediaObj->setCoverPhoto(TRUE);
                        $coverExist = TRUE;
                    }
                }
//                if (!$isComics && !$isEvent && !$isTask) {
//                    if($count > 6 || ($count == 6 && !$coverExist)) {
//                        if ($document->getGalleryType() == "thumbnails") {
//                            $document->setGalleryType("sequence");
//                       }
//                    }
//                }
                $dm->flush();

        }

        $dm->flush();
    }
}
