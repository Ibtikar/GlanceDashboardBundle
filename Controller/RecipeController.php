<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Form\Type\RecipeType;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Service\RecipeOperations;
use Ibtikar\GlanceDashboardBundle\Document\Tag;


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
                    ->field('status')->equals($this->recipeStatus)
                    ->field('deleted')->equals(false);
            if ($this->recipeStatus != 'deleted') {
                $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', "AutoPublish", 'ViewOne'));
                $this->listViewOptions->setBulkActions(array("Delete"));
            } else {
                $this->listViewOptions->setActions(array('Edit', 'Publish', "AutoPublish", 'ViewOne'));
                $this->listViewOptions->setBulkActions(array());
            }
        } else if ($this->listStatus == 'list_deleted_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('assignedTo')->equals(null)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Assign', 'ViewOne'));
        } else if ($this->listStatus == 'list_autopublish_recipe') {
            $queryBuilder = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['autopublish'])
                    ->field('assignedTo')->equals(null)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', "AutoPublish", 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
            $this->listViewOptions->setDefaultSortBy("autoPublishDate");
            $this->listViewOptions->setDefaultSortOrder("desc");
        }

        if (isset($queryBuilder))
            $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:List:recipeList.html.twig");
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

    protected function doList(Request $request)
    {
        $renderingParams = parent::doList($request);
        return $renderingParams;
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
        $newRecipeCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals($this->recipeStatus)
                ->field('assignedTo')->exists(FALSE)
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $assignedRecipeCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals($this->recipeStatus)
                ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        if ($status == RecipeOperations::$TIME_OUT) {
         return new JsonResponse(array('status' => 'error', 'message' => $this->get('translator')->trans('failed operation'),'newRecipe'=>$newRecipeCount,'assignedRecipe'=>$assignedRecipeCount));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_OTHER_USER) {
            return new JsonResponse(array('status' => 'error', 'message' => $this->get('translator')->trans('sorry this recipe assign to other user',array(),  $this->translationDomain),'newRecipe'=>$newRecipeCount,'assignedRecipe'=>$assignedRecipeCount));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_ME) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
            return new JsonResponse(array('status' => 'success', 'message' => $successMessage,'newRecipe'=>$newRecipeCount,'assignedRecipe'=>$assignedRecipeCount));
        }
    }


    public function publishAction(Request $request)
    {
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

            $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($request->get('recipeId'));
            if (!$recipe) {
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }
            $locations = $request->get('locations', array());
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

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('recipe');

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
                                $recipe->addTag($NewTag);
                            } else {
                                $recipe->addTag($tagObject);
                            }
                        }
                    }
                }
                $dm->persist($recipe);

                $dm->flush();

                $this->updateMaterialGallary($recipe, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Recipe:create.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('Add new Recipe', array(), $this->translationDomain),
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
}
