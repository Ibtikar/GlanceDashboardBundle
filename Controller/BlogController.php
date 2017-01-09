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

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('contentType' => 'blog', 'class' => 'dev-page-main-form dev-js-validation form-horizontal'),'type'=>'create'));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('recipe');

                if($formData['related']){
                    $this->updateRelatedRecipe($recipe, $formData['related'],$dm);
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
                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('name' => $tag));
                            if (!$tagObject) {
                                $NewTag = new Tag();
                                $NewTag->setName($tag);
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
                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('name' => $tag));
                            if (!$tagObject) {
                                $NewTag = new Tag();
                                $NewTag->setName($tag);
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

    /**
     *@author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     */
    private function slugifier($blog) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($blog->getTitle()."-".  date('ymdHis'));
        $slugEn = ArabicMongoRegex::slugify($blog->getTitleEn()."-".date('ymdHis'));
        
        $blog->setSlug($slugAr);
        $blog->setSlugEn($slugEn);
        
        $type = strtoupper('type_'.$blog->getType());

        $slug = new Slug();
        $slug->setReferenceId($blog->getId());
        $slug->setType(Slug::$$type);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);
        $dm->flush();
    }


    public function checkMaterialPublishedAction(Request $request) {

        $response = array('status' => 'success', 'valid' => TRUE);

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $em = $this->get('doctrine_mongodb')->getManager();

        $existing = $request->get('existing')!=""?$request->get('existing'):array();
        $fieledValue = strtolower($request->get('fieldValue'));
        $id = strtolower($request->get('id'));


        // check if value is url
        if(strpos($fieledValue,".")){

            $array = explode($this->container->get('router')->getContext()->getHost(), $fieledValue);

            if (count($array) < 2) {
                return new JsonResponse(array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('not valid')));
            }

            $path = trim(str_replace('/ar',"",str_replace("app_dev.php","",array_pop($array))), "/");
            preg_match('/^[a-zA-Z0-9\x{0600}-\x{06ff}\-]+/u', urldecode($path), $slug);

            $material = $em->createQueryBuilder('IbtikarGlanceDashboardBundle:Article')
                ->field('slug')->equals($slug[0])
                    ->getQuery()->getSingleResult();


        } else {
            $material = $em->createQueryBuilder('IbtikarGlanceDashboardBundle:Article')
                ->field('trackingNumber')->equals(trim($request->get('fieldValue')))
                ->getQuery()->getSingleResult();
        }

        $parentMaterial = $em->getRepository('IbtikarGlanceDashboardBundle:Article')->findOneById($id);

        if(is_null($material)){
            $response = array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('not valid'));
        }elseif($material->getId() == $id){
            $response = array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('Sorry, this material cant be linked to itself',array('%type%' => substr($material->getType(),2))),  $this->translationDomain);
        }elseif(in_array($material->getId(), $existing)){
            $response = array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('Sorry, this material is already linked',array('%type%' => substr($material->getType(),2))),  $this->translationDomain);
        }elseif($material->getStatus() == 'deleted'){
            $response = array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('Sorry, this material is deleted',array('%type%' => substr($material->getType(),2))),  $this->translationDomain);
        }elseif($material->getStatus() !== 'publish' && $material->getStatus() !== 'unpublished'){
            $response = array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('Sorry, this material is not published',array('%type%' => substr($material->getType(),2))),  $this->translationDomain);
        }elseif(count($existing) >= 10){
            $response = array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('Sorry, you cant add more than 10 materials'),  $this->translationDomain);
        }

        if($response['valid']){
            $response['title'] = $material->getTitle();
            $response['slug']   = $material->getSlug();
            $response['id']    = $material->getId();
        }

        return new JsonResponse($response);
    }



    public function updateRelatedArticle($document,$relatedJson,$dm = null) {
        if (!$dm) {
            $dm = $this->get('doctrine_mongodb')->getManager();
        }

        $array = json_decode($relatedJson,true);
        foreach($array as $relatedArticle){
            $material = $dm->getRepository('IbtikarGlanceDashboardBundle:Article')->findOneById($relatedArticle['id']);

            if($this->validToRelate($material, $document) && count($document->getRelatedArticle()) < 10){
                    $document->addRelatedArticle($material);
            }

        }


    }



    public function validToRelate($recipe,$document) {

        if($recipe && ($recipe->getStatus() == "publish")){
            if(is_null($document->getRelatedArticle()) || is_array($document->getRelatedArticle())){
                return true;
            }elseif(is_object($document->getRelatedArticle()) && !$document->getRelatedArticle()->contains($recipe)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

       public function checkMaterialPublishedBulkAction(Request $request) {

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $em = $this->get('doctrine_mongodb')->getManager();

        $existing = $request->get('existing')!=""?$request->get('existing'):array();
        $new = $request->get('new');
        $parentId = strtolower($request->get('id'));
        $responseArr = array();

        $materials = $em->createQueryBuilder('IbtikarGlanceDashboardBundle:Article')
            ->field('_id')->in($new)
            ->field('_id')->notIn($existing)
            ->getQuery()->execute();

        foreach ($materials as $material) {
            $id = $material->getId();

            if(is_null($material)){
                $responseArr[] = array('id' => $id, 'valid' => FALSE, 'message' => $this->trans('not valid'));
            }elseif($material->getId() == $parentId){
                $responseArr[] = array('id' => $id, 'valid' => FALSE, 'message' => $material->getTitle()."<br/>".$this->trans('Sorry, this material cant be linked to itself',array('%type%' => substr($material->getType(),2))),$this->translationDomain);
            }elseif(in_array($material->getId(), $existing)){
                $responseArr[] = array('id' => $id, 'valid' => FALSE, 'message' => $material->getTitle()."<br/>".$this->trans('Sorry, this material is already linked',array('%type%' => substr($material->getType(),2))),$this->translationDomain);
            }elseif($material->getStatus() == 'deleted'){
                $responseArr[] = array('id' => $id, 'valid' => FALSE, 'message' => $material->getTitle()."<br/>".$this->trans('Sorry, this material is deleted',array('%type%' => substr($material->getType(),2))),$this->translationDomain);
            }elseif($material->getStatus() !== 'publish' ){
                $responseArr[] = array('id' => $id, 'valid' => FALSE, 'message' => $material->getTitle()."<br/>".$this->trans('Sorry, this material is not published',array('%type%' => substr($material->getType(),2))),$this->translationDomain);
            }elseif(count($existing) >= 10){
                $responseArr[] = array('id' => $id, 'valid' => FALSE, 'message' => $material->getTitle()."<br/>".$this->trans('Sorry, you cant add more than 10 materials'),array(),$this->translationDomain);
                break;
            }else{
                $responseArr[] = array(
                        'status' => 'success',
                        'valid' => TRUE,
                        'title' => $material->getTitle(),
                        'slug'  => $material->getSlug(),
                        'id'    => $id
                );
            }
        }

        return new JsonResponse(array('data' => $responseArr));
    }

       public function searchRelatedAction(Request $request) {
//        die(var_dump($request->request->all(),$request->query->all()));

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:Article');

        $searchString = trim($request->get('q'));
        $oldvalue = json_decode(trim($request->get('old')), true);

        if (count($oldvalue) >= 10) {
            return new JsonResponse(array(array(
                'message' => $this->trans('recipe must less than 10',array(),  $this->translationDomain))
            ));
        }

        if(strpos($searchString,".")){

            $array = explode($this->container->get('router')->getContext()->getHost(), $searchString);

            if (count($array) < 2) {
                return new JsonResponse(array('status' => 'success', 'valid' => FALSE, 'message' => $this->trans('not valid')));
            }

            $path = trim(str_replace("app_dev.php","",array_pop($array)), "/");
//            die(var_dump(urldecode($path)));
            preg_match_all('/[a-zA-Z0-9\x{0600}-\x{06ff}\-]+/u', urldecode($path), $slug);

            if(isset($slug[0][1])){
                $queryBuilder->addOr($queryBuilder->expr()->field('slug')->equals($slug[0][1]));
                $queryBuilder->addOr($queryBuilder->expr()->field('slugEn')->equals($slug[0][1]));
            }

        }elseif ($searchString && strlen($searchString) >= 1) {
            $searchRegex = new \MongoRegex('/' . preg_quote($searchString) . '/');
            $queryBuilder->addOr($queryBuilder->expr()->field('title')->equals($searchRegex));
            $queryBuilder->addOr($queryBuilder->expr()->field('titleEn')->equals($searchRegex));
        }
        $existingIds=array();
        if($oldvalue){
        foreach($oldvalue as $value){
            $existingIds[]=$value['id'];
        }
        }
        $queryBuilder->field('status')->equals('publish')
                ->field('type')->equals('recipe')
                ->field('id')->notIn($existingIds)
                ->limit(10)
                ->sort('createdAt', 'DESC');

        $result = $queryBuilder->getQuery()->toArray();
        $responseArr = array();

        foreach($result as $recipe){
            $responseArr[] = array(
                'id' => $recipe->getId(),
                'text' => $recipe->getTitle(),
                'img' => $recipe->getDefaultCoverPhoto()?$recipe->getDefaultCoverPhoto()->getWebPath():""
            );
        }

        return new JsonResponse($responseArr);
    }

    public function relatedMaterialDeleteAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $parent = $request->get('parent');
        $child = $request->get('child');

        $materialParent = $dm->getRepository('IbtikarGlanceDashboardBundle:Article')->findOneById($parent);
        $materialChild = $dm->getRepository('IbtikarGlanceDashboardBundle:Article')->findOneById($child);

        if($materialParent && $materialChild && $materialParent->getRelatedArticle()->contains($materialChild)){
            $materialParent->removeRelatedArticle($materialChild);
            $dm->flush();
        }
            $response = array('status' => 'success','message' => $this->trans('done sucessfully'));


        return new JsonResponse($response);

    }

    protected function validateDelete(Document $document){
        //invalid material OR user who wants to forward is not the material owner
        if ($document->getStatus() == 'deleted') {
            if ($document->getType() == 'recipe') {
                return $this->trans('already deleted', array(), 'recipe');
            } elseif ($document->getType() == 'article') {
                return  $this->trans('article already deleted', array(), 'recipe');
            } else {
                return  $this->trans('tip already deleted', array(), 'recipe');
            }
        }

    }

//     /**
//     * @author Ola <ola.ali@ibtikar.net.sa>
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     */
//    public function editAction(Request $request, $id) {
//
//        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Article','link'=>$this->generateUrl('ibtikar_glance_dashboard_recipe_create')),
////            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Product')
//        );
//        $breadCrumbArray = $this->preparedMenu($menus);
//        $dm = $this->get('doctrine_mongodb')->getManager();
//
//        $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Article')->find($id);
//        if (!$recipe) {
//            throw $this->createNotFoundException($this->trans('Wrong id'));
//        }
//        $tagSelected = $this->getTagsForDocument($recipe);
//        $tagSelectedEn = $this->getTagsEnForDocument($recipe);
//        $form = $this->createForm(ArticleType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal'
//            ),'type'=>'edit','tagSelected'=>$tagSelected,'tagEnSelected'=>$tagSelectedEn));
//
//        if ($request->getMethod() === 'POST') {
//            $form->handleRequest($request);
//
//
//            if ($form->isValid()) {
//                $formData = $request->get('recipe');
//
////                if($formData['relatedArticle']){
////                    $this->updateRelatedMaterial($recipe, $formData['relatedArticle'],$dm);
////                }
//
//                $tags = $formData['tags'];
//                $tagsEn = $formData['tagsEn'];
//
//                $recipe->setTags();
//                $recipe->setTagsEn();
//
//                if ($tags) {
//                    $tagsArray = explode(',', $tags);
//                    $tagsArray = array_unique($tagsArray);
//                    foreach ($tagsArray as $tag) {
//                        $tag = trim($tag);
//                        if (mb_strlen($tag, 'UTF-8') <= 330) {
//                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('name' => $tag));
//                            if (!$tagObject) {
//                                $NewTag = new Tag();
//                                $NewTag->setName($tag);
//                                $dm->persist($NewTag);
//                                $recipe->addTag($NewTag);
//                            } else {
//                                $recipe->addTag($tagObject);
//                            }
//                        }
//                    }
//                }
//                if ($tagsEn) {
//                    $tagsArray = explode(',', $tagsEn);
//                    $tagsArray = array_unique($tagsArray);
//                    foreach ($tagsArray as $tag) {
//                        $tag = trim($tag);
//                        if (mb_strlen($tag, 'UTF-8') <= 330) {
//                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('name' => $tag));
//                            if (!$tagObject) {
//                                $NewTag = new Tag();
//                                $NewTag->setName($tag);
//                                $dm->persist($NewTag);
//                                $recipe->addTagEn($NewTag);
//                            } else {
//                                $recipe->addTagEn($tagObject);
//                            }
//                        }
//                    }
//                }
//                $dm->persist($recipe);
////                $this->slugifier($recipe);
//
//                $dm->flush();
//
//                $this->updateMaterialGallary($recipe, $formData['media'], $dm);
//
//                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
//
//                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_'.  strtolower($this->calledClassName).'_list_'.$recipe->getStatus().'_recipe'), array(), true));
//
//            }else{
//                $errors=array();
//                  foreach ($form->getErrors() as $errorObject) {
//            $error[] = $errorObject->getMessage();
//
//             }
//
//            }
//        }
//        return $this->render('IbtikarGlanceDashboardBundle:Article:edit.html.twig', array(
//                'form' => $form->createView(),
//                'room' => $this->calledClassName,
//                'breadcrumb' => $breadCrumbArray,
//                'title' => $this->trans('edit recipe', array(), $this->translationDomain),
//                'translationDomain' => $this->translationDomain
//        ));
//    }

}
