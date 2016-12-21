<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Form\Type\RecipeType;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Service\RecipeOperations;
use Ibtikar\GlanceDashboardBundle\Document\Tag;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Ibtikar\GlanceDashboardBundle\Document\Document;

class RecipeController extends BackendController
{

    protected $translationDomain = 'recipe';
    protected $recipeStatus = 'new';
    protected $listName;
    protected $listStatus;
    protected $sublistName = 'New';


    protected function configureListColumns()
    {
        $this->allListColumns = array(
            "title" => array("searchFieldType" => "input"),
            "titleEn" => array("type" => "translated"),
            "country" => array("isSortable" => false),
            "createdBy" => array("isSortable" => false),
            "createdAt" => array("type" => "date"),
            "updatedAt" => array("type" => "date"),
            "course" => array("isSortable"=>false,"searchFieldType"=>"select",'type'=>'many'),
            "chef" => array("isSortable" => false),
        );
        $this->defaultListColumns = array(
            "title",
            "createdAt",
            "chef",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $request)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        if ($this->listStatus == 'list_new_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['new'])
                    ->field('assignedTo')->exists(FALSE)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Assign', 'ViewOne'));
        } else if ($this->listStatus == 'list_assigned_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                    ->field('status')->equals(Recipe::$statuses['new'])
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
        } else if ($this->listStatus == 'list_deleted_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Publish', 'ViewOne'));
        } else if ($this->listStatus == 'list_autopublish_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['autopublish'])
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
            $this->listViewOptions->setDefaultSortBy("autoPublishDate");
            $this->listViewOptions->setDefaultSortOrder("desc");
        } else if ($this->listStatus == 'list_publish_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['publish'])
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
            $this->listViewOptions->setDefaultSortBy("publishedAt");
            $this->listViewOptions->setDefaultSortOrder("desc");
        }

        if (isset($queryBuilder))
            $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Recipe:recipeList.html.twig");
    }

    public function listNewRecipeAction(Request $request)
    {
        $this->listStatus = 'list_new_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;


        return parent::listAction($request);
    }

    public function listDeletedRecipeAction(Request $request)
    {
        $this->listStatus = 'list_deleted_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;


        return parent::listAction($request);
    }

    public function listAssignedRecipeAction(Request $request)
    {
        $this->listStatus = 'list_assigned_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::listAction($request);
    }

    public function listautopublishRecipeAction(Request $request)
    {
        $this->listStatus = 'list_autopublish_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::listAction($request);
    }

    public function listpublishRecipeAction(Request $request)
    {
        $this->listStatus = 'list_publish_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::listAction($request);
    }

    public function changeListNewRecipeColumnsAction(Request $request)
    {
        $this->listStatus = 'list_new_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }
    public function changeListDeletedRecipeColumnsAction(Request $request)
    {
        $this->listStatus = 'list_deleted_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    public function changeListAssignedRecipeColumnsAction(Request $request)
    {
        $this->listStatus = 'list_assigned_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    public function changeListAutopublishRecipeColumnsAction(Request $request)
    {
        $this->listStatus = 'list_autopublish_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }
    public function changeListPublishRecipeColumnsAction(Request $request)
    {
        $this->listStatus = 'list_publish_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    protected function doList(Request $request)
    {
        $renderingParams = parent::doList($request);
        return $this->getTabCount($renderingParams);
    }


    public function assignToMeAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ASSIGN') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table','message'=>$this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        $recipeId = $request->get('recipeId');
        $status = $this->get('recipe_operations')->assignToMe($recipeId,  $this->recipeStatus);
        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($status == RecipeOperations::$TIME_OUT) {
         return new JsonResponse(array_merge(array('status' => 'error', 'message' => $this->get('translator')->trans('failed operation')),$this->getTabCount()));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_OTHER_USER) {
            return new JsonResponse(array_merge(array('status' => 'error', 'message' => $this->get('translator')->trans('sorry this recipe assign to other user',array(),  $this->translationDomain)),$this->getTabCount()));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_ME) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
            return new JsonResponse(array_merge(array('status' => 'success', 'message' => $successMessage),  $this->getTabCount()));
        }
    }


    public function publishAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $securityContext = $this->get('security.authorization_checker');
        $publishOperations = $this->get('recipe_operations');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table','message'=>$this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }

        if ($request->getMethod() === 'GET') {
            $id = $request->get('id');
            if (!$id) {
                return $this->getFailedResponse();
            }

            $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($id);
            if (!$recipe)
                throw $this->createNotFoundException($this->trans('Wrong id'));

            $currentPublishedLocations = array();
            $locations = array();
            foreach ($recipe->getPublishLocations() as $location) {
                $currentPublishedLocations[] = $location->getSection();
            }




            $allowedLocations = $publishOperations->getAllowedLocations($recipe);

            foreach ($allowedLocations as $location) {

                $locations[] = $location;
            }
            $autoPublishDate = '';

            if ($recipe->getAutoPublishDate()) {
                $autoPublishDate = $recipe->getAutoPublishDate()->format('m/d/Y H:i A');
            }


            return $this->render('IbtikarGlanceDashboardBundle:Recipe:publishModal.html.twig', array(
                    'autoPublishDate' => $autoPublishDate,
                    'translationDomain' => $this->translationDomain,
                    'locations' => $locations,
                    'currentLocations' => $currentPublishedLocations,
                    'document' => $recipe
            ));
        } else if ($request->getMethod() === 'POST') {

            $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($request->get('documentId'));
            if (!$recipe) {
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }
            $locations = $request->get('publishLocation', array());
            if (!empty($locations)) {
                $locations = $dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('id' => array('$in' => $request->get('publishLocation'))));
            }

            $recipeStatus = $recipe->getStatus();
            $status = $request->get('status');
            if ($status != $recipeStatus) {
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }


            switch ($recipeStatus) {
                case 'new':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($recipe, $locations);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->autoPublish($recipe, $locations, $autoPublishDate);
                    }
                    break;
                case 'publish':
                    $publishResult = $publishOperations->managePublishControl($recipe, $locations);
                    break;
                case 'deleted':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($recipe, $locations, TRUE);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->autoPublish($recipe, $locations, $autoPublishDate);
                    }
                    break;
                case 'autopublish':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($recipe, $locations);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->manageAutoPublishControl($recipe, $locations, $autoPublishDate);
                    }
                    break;
            }



            return new JsonResponse(array_merge($publishResult, $this->getTabCount()));
        }
    }

     public function getTabCount($renderingParams = array())
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $renderingParams['newRecipeCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['new'])
                ->field('assignedTo')->exists(FALSE)
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $renderingParams['publishRecipeCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['publish'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $renderingParams['autopublishRecipeCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['autopublish'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $renderingParams['deletedRecipeCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['deleted'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $renderingParams['assignedRecipeCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                ->field('status')->equals(Recipe::$statuses['new'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        return $renderingParams;
    }




    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {

        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Recipe'),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Recipe')
        );
        $breadCrumbArray = $this->preparedMenu($menus);

        $recipe = new Recipe();
        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal'),'type'=>'create'));

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
        return $this->render('IbtikarGlanceDashboardBundle:Recipe:create.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('Add new Recipe', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction(Request $request)
    {


        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');

        if (!$securityContext->isGranted('ROLE_ADMIN') && !$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
            $id = $request->get('id', '');
            if ($id) {
                $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($id);
                if (!$recipe) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
            }
        $tagSelected = $this->getTagsForDocument($recipe);
        $tagSelectedEn = $this->getTagsForDocument($recipe,"en");

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

        $form->get('tags')->setData($tagSelected);
        $form->get('tagsEn')->setData($tagSelectedEn);


        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('form');
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_recipe_list'), array(), true));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Recipe:edit.html.twig', array(
                'recipe' => $recipe,
                'form' => $form->createView(),
                'title' => $this->trans('Edit Recipe', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * rename folder with material name and add material to media documents
     *
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     */

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

    public function getTagsAction()
    {
        $tags = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Tag')->findAll();
        $responseContent = array();
        foreach ($tags as $tag) {
//            $responseContent[] = array("id"=>$tag->getId(),"text"=>$tag->getName());
            $responseContent[] = $tag->getName();
        }
        return new JsonResponse($responseContent);
    }

    public function deleteAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');

        if (!$securityContext->isGranted('ROLE_ADMIN') && !$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        if ($request->getMethod() === 'GET') {
            $id = $request->get('id', '');
            if ($id) {
                $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($id);
                if (!$recipe) {
                    $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
                    return new JsonResponse($result);
                }
            }
            if ($this->calledClassName != 'recipepublish') {
                if ($id) {
                    $msg = str_replace(array('%title%','%type%'),array($recipe->getTitle(),  $this->trans($recipe->getType(), array(), $this->translationDomain)) , $this->trans('delete single %type% %title%', array(), $this->translationDomain));
                    $url=  $this->generateUrl('ibtikar_glance_dashboard_'.strtolower($this->calledClassName).'_delete',array('id'=>$id));
                } else {
                    $msg = str_replace(array('%no%',),$request->get('count') , $this->trans(str_replace('%type%',$request->get('type'),'delete multiple %type% %no%'), array(), $this->translationDomain));
                    $url=  $this->generateUrl('ibtikar_glance_dashboard_'.strtolower($this->calledClassName).'_bulk_actions');

                }
            } else {
                if ($id) {
                   $msg = str_replace(array('%title%','%type%'),array($recipe->getTitle(),  $this->trans($recipe->getType(), array(), $this->translationDomain)) , $this->trans('delete single publish %type% %title%', array(), $this->translationDomain));
                   $url=  $this->generateUrl('ibtikar_glance_dashboard_'.strtolower($this->calledClassName).'_delete',array('id'=>$id));

                } else {
                    $msg =  str_replace('%no%', $request->get('count'), $this->trans(str_replace('%type%',$request->get('type'),'delete multiple publish %type% %no%'), array(), $this->translationDomain));
                    $url=  $this->generateUrl('ibtikar_glance_dashboard_'.strtolower($this->calledClassName).'_bulk_actions');

                }
            }
            return $this->render('IbtikarGlanceDashboardBundle:Recipe:deleteModal.html.twig', array(
                    'translationDomain' => $this->translationDomain,
                    'url' => $url,
                    'msg' => $msg,
            ));
        } else if ($request->getMethod() === 'POST') {
            $recipe = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('id')->equals($request->get('id'))
                    ->field('deleted')->equals(false)
                    ->getQuery()->getSingleResult();

            $forwardResult = $this->get('recipe_operations')->delete($recipe, $request->get('reason'));


            return new JsonResponse(array_merge(array('status' => $forwardResult["status"], 'message' => $forwardResult["message"]),$this->getTabCount()));
        }
    }


    public function bulkAction(Request $request)
    {
        $securityContext = $this->get('security.authorization_checker');
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $ids = array_diff($request->get('ids', array()), array(""));
        if (empty($ids)) {
            return $this->getFailedResponse();
        }
        $bulkAction = $request->get('bulk-action');

        if (!$bulkAction) {
            return $this->getFailedResponse();
        }

        $successIds = array();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $documents = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('id' => array('$in' => array_values($ids))));
        $translator = $this->get('translator');
        $message = str_replace(array('%action%', '%item-translation%', '%ids-count%'), array($translator->trans($bulkAction), $this->trans('recipe',array(),$this->translationDomain), count($ids)), $translator->trans('successfully %action% %success-count% %item-translation% from %ids-count%.'));
        $foundDocumentsIds = array();
        foreach ($documents as $document) {
            $foundDocumentsIds [] = $document->getId();
        }
        $deletedIds = array_diff($ids, $foundDocumentsIds);
        $data = array(
            'status' => 'success',
            'message' => '',
            'bulk-action' => $bulkAction,
            'success' => &$successIds,
            'errors' => array()
        );
        $data['errors'][$translator->trans('Already deleted.')] = $deletedIds;
        if (count($deletedIds) === count($ids)) {
            $data['message'] = str_replace('%success-count%', 0, $message);
            return new JsonResponse($data);
        }

        switch ($bulkAction) {
            case 'Delete':
                $permission = 'ROLE_' . strtoupper($this->calledClassName) . '_DELETE';

                if (!$securityContext->isGranted($permission) && !$securityContext->isGranted('ROLE_ADMIN')) {
                    $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
                    return new JsonResponse($result);
                }
                foreach ($documents as $document) {
                    $errorMessage = $this->validateDelete($document);

                    if ($document->getNotModified()) {
                        $data['errors'][$translator->trans('failed operation')] [] = $document->getId();
                        continue;
                    }
                    if ($errorMessage) {
                        $data['errors'][$errorMessage] [] = $document->getId();
                        continue;
                    }

                    try {


                        $forwardResult = $this->get('recipe_operations')->delete($document, $request->get('reason'));
                        $dm->flush();
                        $successIds [] = $document->getId();
                    } catch (\Exception $e) {
                        $data['errors'][$translator->trans('failed operation')] [] = $document->getId();
                    }
                }

                break;
        }
        $data['message'] = str_replace('%success-count%', count($successIds), $message);
        return new JsonResponse(array_merge($data, $this->getTabCount()));
    }

     public function getListJsonData($request,$renderingParams)
    {
        $documentObjects = array();
        foreach ($renderingParams['pagination'] as $document) {
            $oneDocument = array();

            foreach ($renderingParams['columnArray'] as $value) {
                if ($value == 'id') {
                    $oneDocument['id'] = '<div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="ids[]"  data-type="' . $document->getType() . '" class="styled dev-checkbox" value="' . $document->getId() . '">
                                    </label>
                              </div>';
                    continue;
                }
                if ($value == 'actions') {
                    $security = $this->container->get('security.authorization_checker');
                    $actionTd = '';

                    if ($this->listViewOptions->hasActionsColumn($this->calledClassName)) {
                        foreach ($this->listViewOptions->getActions() as $action) {
                            if ($action == 'Edit' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_EDIT')) && !$document->getNotModified()) {
                                $actionTd.= '<a class="btn btn-defualt"  href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_edit', array('id' => $document->getId())) . '" ><i class="icon-pencil" data-popup="tooltip" title="' . $this->trans('Edit ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-placement="right"></i></a>';
                            } elseif ($action == 'Delete' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE')) && !$document->getNotModified()) {
                                $actionTd.= '<a class="btn btn-defualt dev-delete-single-recipe"  data-href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_delete', array('id' => $document->getId())) . '" data-id="'. $document->getId().'" ><i class="icon-trash" data-popup="tooltip" title="' . $this->trans('Delete ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-placement="right"></i></a>';
                            } elseif ($action == 'ViewOne' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_VIEWONE'))) {
                                $actionTd.= '<a class="btn btn-defualt"  href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_view', array('id' => $document->getId())) . '" ><i class="icon-eye" data-popup="tooltip"  title="' . $this->trans('View One ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"  data-placement="right" ></i></a>';
                            } elseif ($action == 'Assign' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ASSIGN'))) {
                                $actionTd.= '<a class="btn btn-defualt dev-assign-to-me" href="javascript:void(0);"  data-url="'.$this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_assign_to_me').'" data-id="'.$document->getId().'"><i class="icon-user"  title="' . $this->trans('AssignToMe', array(), $this->translationDomain) . '"  data-popup="tooltip" data-placement="right"></i></a>';
                            } elseif ($action == 'Publish' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH'))) {
                                $actionTd.= '<a href="javascript:void(0)" data-toggle="modal"  class="btn btn-defualt dev-publish-recipe" data-id="'.$document->getId().'"><i class="icon-share" data-placement="right"  data-popup="tooltip" title="' . $this->trans('publish ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"></i></a>
';
                            }
//                            elseif ($action == 'AutoPublish' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_AUTOPUBLISH'))) {
//                                $actionTd.= '<a class="btn btn-defualt" href="javascript:void(0);" title="'  . $this->trans('autopublish ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-popup="tooltip"  data-placement="bottom" ><i class="icon-checkmark3"></i></a>';
//                            }
                        }

                        $oneDocument['actions'] = $actionTd;
                        continue;
                    }
                }
                $getfunction = "get" . ucfirst($value);
                if ($value == 'name' && $document instanceof \Ibtikar\GlanceUMSBundle\Document\Role) {
                    $oneDocument[$value] = '<a class="dev-role-getPermision" href="javascript:void(0)" data-id="' . $document->getId() . '">' . $document->$getfunction() . '</a>';
                }
                elseif ($value == 'username') {
                    $image = $document->getWebPath();
                    if (!$image) {
                        $image = 'bundles/ibtikarshareeconomydashboarddesign/images/profile.jpg';
                    }
                    $oneDocument[$value] = '<div class="media-left media-middle"><a href="javascript:void(0)">'
                        . '<img src="/' . $image . '" class="img-circle img-lg" alt=""></a></div>
                                                <div class="media-body">
                                                    <a href="javascript:void(0);" class="display-inline-block text-default text-semibold letter-icon-title">  ' . $document->$getfunction() . ' </a>
                                                </div>';
                }
                elseif ($value == 'profilePhoto') {
                    $image = $document->getProfilePhoto();
                    if (!$image) {
                        $image = 'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg';
                    } else {
                        $image = $image->getWebPath();
                    }
                    $oneDocument[$value] = '<div class="media-left media-middle"><a href="javascript:void(0)">'
                        . '<img src="/' . $image . '" class="img-lg" alt=""></a></div>';
                } elseif ($document->$getfunction() instanceof \DateTime) {
                    $oneDocument[$value] = $document->$getfunction() ? $document->$getfunction()->format('Y-m-d') : null;
                } elseif (is_array($document->$getfunction()) || $document->$getfunction() instanceof \Traversable) {
                    $elementsArray = array();
                    foreach ($document->$getfunction() as $element) {
                        if ($value == 'course') {
                            $elementsArray[] = is_object($element) ? $element->__toString() : $this->trans($element, array(), $this->translationDomain);
                            continue;
                        }
                        $elementsArray[] = is_object($element) ? $element->__toString() : $element;
                    }
                    $oneDocument[$value] = implode(',', $elementsArray);
                } else {
                    $fieldData = $document->$getfunction();
                    $oneDocument[$value] = is_object($fieldData) ? $fieldData->__toString() : $this->getShortDescriptionString($fieldData);
                }
            }

            $documentObjects[] = $oneDocument;
        }
        $rowsHeader=$this->getColumnHeaderAndSort($request);
        return new JsonResponse(array('status' => 'success','data' => $documentObjects, "draw" => 0, 'sEcho' => 0,'columns'=>$rowsHeader['columnHeader'],
            "recordsTotal" => $renderingParams['total'],
            "recordsFiltered" => $renderingParams['total']));
    }

    /**
     *@author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     */
    private function slugifier($recipe) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($recipe->getTitle()."-".  date('ymdHis'));
        $slugEn = ArabicMongoRegex::slugify($recipe->getTitleEn()."-".date('ymdHis'));
        $recipe->setSlug($slugAr);
        $recipe->setSlugEn($slugEn);

        $slug = new Slug();
        $slug->setReferenceId($recipe->getId());
        $slug->setType(Slug::$TYPE_RECIPE);
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

            $material = $em->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('slug')->equals($slug[0])
                    ->getQuery()->getSingleResult();


        } else {
            $material = $em->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('trackingNumber')->equals(trim($request->get('fieldValue')))
                ->getQuery()->getSingleResult();
        }

        $parentMaterial = $em->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($id);

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



    public function updateRelatedRecipe($document,$relatedJson,$dm = null) {
        if (!$dm) {
            $dm = $this->get('doctrine_mongodb')->getManager();
        }

        $array = json_decode($relatedJson,true);
        foreach($array as $relatedRecipe){
            $material = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($relatedRecipe['id']);

            if($this->validToRelate($material, $document) && count($document->getRelatedRecipe()) < 10){
                    $document->addRelatedRecipe($material);
            }

        }


    }



    public function validToRelate($recipe,$document) {

        if($recipe && ($recipe->getStatus() == "publish")){
            if(is_null($document->getRelatedRecipe()) || is_array($document->getRelatedRecipe())){
                return true;
            }elseif(is_object($document->getRelatedRecipe()) && !$document->getRelatedRecipe()->contains($recipe)){
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

        $materials = $em->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
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

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe');

        $searchString = trim($request->get('q'));

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

        $queryBuilder->field('status')->equals('publish')
                ->field('type')->equals('recipe')
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

        $materialParent = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($parent);
        $materialChild = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($child);

        if($materialParent && $materialChild && $materialParent->getRelatedRecipe()->contains($materialChild)){
            $materialParent->removeRelatedRecipe($materialChild);
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
//        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Recipe','link'=>$this->generateUrl('ibtikar_glance_dashboard_recipe_create')),
////            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Product')
//        );
//        $breadCrumbArray = $this->preparedMenu($menus);
//        $dm = $this->get('doctrine_mongodb')->getManager();
//
//        $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($id);
//        if (!$recipe) {
//            throw $this->createNotFoundException($this->trans('Wrong id'));
//        }
//        $tagSelected = $this->getTagsForDocument($recipe);
//        $tagSelectedEn = $this->getTagsEnForDocument($recipe);
//        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal'
//            ),'type'=>'edit','tagSelected'=>$tagSelected,'tagEnSelected'=>$tagSelectedEn));
//
//        if ($request->getMethod() === 'POST') {
//            $form->handleRequest($request);
//
//
//            if ($form->isValid()) {
//                $formData = $request->get('recipe');
//
////                if($formData['relatedRecipe']){
////                    $this->updateRelatedMaterial($recipe, $formData['relatedRecipe'],$dm);
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
//        return $this->render('IbtikarGlanceDashboardBundle:Recipe:edit.html.twig', array(
//                'form' => $form->createView(),
//                'room' => $this->calledClassName,
//                'breadcrumb' => $breadCrumbArray,
//                'title' => $this->trans('edit recipe', array(), $this->translationDomain),
//                'translationDomain' => $this->translationDomain
//        ));
//    }

}
