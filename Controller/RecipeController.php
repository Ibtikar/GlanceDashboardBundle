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
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', "AutoPublish", 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
        } else if ($this->listStatus == 'list_deleted_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Assign', 'ViewOne'));
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
         return new JsonResponse(array('status' => 'error', 'message' => $this->get('translator')->trans('failed operation'),'newRecipe'=>$newRecipeCount,'assignedRecipe'=>$assignedRecipeCount));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_OTHER_USER) {
            return new JsonResponse(array('status' => 'error', 'message' => $this->get('translator')->trans('sorry this recipe assign to other user',array(),  $this->translationDomain),'newRecipe'=>$newRecipeCount,'assignedRecipe'=>$assignedRecipeCount));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_ME) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
            return new JsonResponse(array_merge(array('status' => 'success', 'message' => $successMessage),  $this->getTabCount()));
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
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        return $renderingParams;
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
                                        <input type="checkbox" name="ids[]" class="styled dev-checkbox" value="' . $document->getId() . '">
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
                            } elseif ($action == 'Delete' && $document->getStatus() != 'deleted' && !$document->getNotModified()) {
                                if (($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper('recipe' . $document->getStatus()) . '_DELETE'))) {
                                    $actionTd.= '<a class="btn btn-defualt"  data-href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower('recipe' . $document->getStatus()) . '_delete', array('id' => $document->getId())) . '" ' . str_replace('%title%', $document, $this->get('app.twig.popover_factory_extension')->popoverFactory(isset($renderingParams['deletePopoverConfig']) ? $renderingParams['deletePopoverConfig'] : [])) . '" ><i class="icon-trash" data-popup="tooltip" title="' . $this->trans('Delete ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-placement="right"></i></a>';
                                }
                            } elseif ($action == 'ViewOne' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_VIEWONE'))) {
                                $actionTd.= '<a class="btn btn-defualt"  href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_view', array('id' => $document->getId())) . '" ><i class="icon-eye" data-popup="tooltip"  title="' . $this->trans('View One ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"  data-placement="right" ></i></a>';
                            } elseif ($action == 'Assign' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ASSIGN'))) {
                                $actionTd.= '<a class="btn btn-defualt" href="javascript:void(0);"  ><i class="icon-user"  title="' . $this->trans('AssignToMe', array(), $this->translationDomain) . '"  data-popup="tooltip" data-placement="right"></i></a>';
                            } elseif ($action == 'Publish' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH'))) {
                                $actionTd.= '<a href="javascript:void(0)" data-toggle="modal"  class="btn btn-defualt dev-publish-recipe" data-id="' . $document->getId() . '"><i class="icon-share" data-placement="right"  data-popup="tooltip" title="' . $this->trans('Edit ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"></i></a>
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
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Recipe:create.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('Add new Recipe', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
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
