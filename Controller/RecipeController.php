<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Form\Type\RecipeType;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Service\RecipeOperations;
use Ibtikar\GlanceDashboardBundle\Document\RecipeTag;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\History;

class RecipeController extends BackendController
{

    protected $translationDomain = 'recipe';
    protected $recipeStatus = 'draft';
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
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne','draft'));
            $this->listViewOptions->setBulkActions(array("Delete"));
        } else if ($this->listStatus == 'list_deleted_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Publish', 'ViewOne','draft'));
        } else if ($this->listStatus == 'list_autopublish_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['autopublish'])
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne','draft'));
            $this->listViewOptions->setBulkActions(array("Delete"));
            $this->listViewOptions->setDefaultSortBy("autoPublishDate");
            $this->listViewOptions->setDefaultSortOrder("desc");
        }
        else if ($this->listStatus == 'list_publish_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['publish'])
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne','draft'));
            $this->listViewOptions->setBulkActions(array("Delete"));
            $this->listViewOptions->setDefaultSortBy("publishedAt");
            $this->listViewOptions->setDefaultSortOrder("desc");
        }
        else if ($this->listStatus == 'list_draft_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals(Recipe::$statuses['draft'])
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
            $this->listViewOptions->setDefaultSortBy("createdAt");
            $this->listViewOptions->setDefaultSortOrder("desc");
        }

        //search parameters query
        if ($request->get('title')) {
            $queryBuilder->addAnd($queryBuilder->expr()->addOr(
                        $queryBuilder->expr()->field('title')->equals(new \MongoRegex(('/' .  preg_quote($request->get('title')) . '/i')))
                        )->addOr(
                        $queryBuilder->expr()->field('titleEn')->equals(new \MongoRegex(('/' .  preg_quote($request->get('title')) . '/i')))
                        )
                    );
        }
        if ($request->get('chef')) {
            $queryBuilder = $queryBuilder->field('chef.$id')->equals(new \MongoId($request->get('chef')));
        }

        if ($request->get('from') && (bool) strtotime($request->get('from'))) {
            $queryBuilder = $queryBuilder->field('createdAt')->gte(new \DateTime($request->get('from')));
        }
        if ($request->get('to') && (bool) strtotime($request->get('to'))) {
            $fromDate = new \DateTime($request->get('to'));
            $queryBuilder->field('createdAt')->lte($fromDate->modify('+1 day'));
        }
        if ($request->get('tags')) {
            $tags = explode(',', $request->get('tags'));
            $queryBuilder->addAnd($queryBuilder->expr()->addOr(
                        $queryBuilder->expr()->field('recipeTags')->all($tags)
                        )
                    );
        }

        if ($request->get('meals')) {
            $meals = explode(',', $request->get('meals'));
            foreach ($meals as $meal) {
                $queryBuilder->field('meal.'.$meal)->exists(true);
            }
        }

        if ($request->get('ingredients')) {
            $ingredients = explode(',', $request->get('ingredients'));
            foreach ($ingredients as $ingredient) {
                $queryBuilder->field('keyIngredient.'.$ingredient)->exists(true);
            }
        }

        if ($request->get('type')) {
            $queryBuilder->field('type')->in($request->get('type'));
        }

        if ($request->get('products')) {
            $products = explode(',', $request->get('products'));
            $queryBuilder->field('products')->all($products);
        }

        if($this->listStatus == 'list_publish_recipe'){
            if ($request->get('pub-from') && (bool) strtotime($request->get('pub-from'))) {
                $queryBuilder = $queryBuilder->field('publishedAt')->gte(new \DateTime($request->get('pub-from')));
            }
            if ($request->get('pub-to') && (bool) strtotime($request->get('pub-to'))) {
                $fromDate = new \DateTime($request->get('pub-to'));
                $queryBuilder->field('publishedAt')->lte($fromDate->modify('+1 day'));
            }
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

    public function listDraftRecipeAction(Request $request)
    {
        $this->listStatus = 'list_draft_recipe';
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

    public function changeListDraftRecipeColumnsAction(Request $request)
    {
        $this->listStatus = 'list_draft_recipe';
        $this->listName = 'recipe' . $this->recipeStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    protected function doList(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $renderingParams = parent::doList($request);

        $renderingParams['title_selected'] = $request->get('title');
        $renderingParams['chef_selected'] = $request->get('chef');
        $renderingParams['dateFrom_selected'] = $request->get('from');
        $renderingParams['dateTo_selected'] = $request->get('to');
        $renderingParams['tags_selected'] = $request->get('tags');
        $renderingParams['meals_selected'] = $request->get('meals');
        $renderingParams['ingredients_selected'] = $request->get('ingredients');
        $renderingParams['products_selected'] = $request->get('products');
        $renderingParams['publishedFrom_selected'] = $request->get('pub-from');
        $renderingParams['publishedTo_selected'] = $request->get('pub-to');
        $renderingParams['search'] = FALSE;

        if ($renderingParams['title_selected'] || $renderingParams['chef_selected'] || $renderingParams['dateFrom_selected'] || $renderingParams['dateTo_selected'] || $renderingParams['tags_selected'] || $renderingParams['meals_selected'] || $renderingParams['ingredients_selected'] || $renderingParams['products_selected'] || $renderingParams['publishedFrom_selected'] || $renderingParams['publishedTo_selected']) {
            $renderingParams['search'] = TRUE;
        }

        $renderingParams['chefs'] = $dm->getRepository('IbtikarGlanceUMSBundle:Staff')->findAll();
        $renderingParams['tags'] = $dm->getRepository('IbtikarGlanceDashboardBundle:RecipeTag')->findAll();
        $renderingParams['meals'] = Recipe::$mealMap;
        $renderingParams['ingredients'] = Recipe::$keyIngredientMap;
        $renderingParams['products'] = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->findAll();
        return $this->getTabCount($renderingParams);
    }


    public function contentCountAction(Request $request)
    {
        $renderingParams['draftRecipeCountRecipe'] = $this->buildQueryBuilder($request)->field('type')->equals(Recipe::$types['recipe'])->getQuery()->execute()->count();
        $renderingParams['draftRecipeCountArticle'] = $this->buildQueryBuilder($request)->field('type')->equals(Recipe::$types['article'])->getQuery()->execute()->count();
        $renderingParams['draftRecipeCountTip'] = $this->buildQueryBuilder($request)->field('type')->equals(Recipe::$types['tip'])->getQuery()->execute()->count();
        $renderingParams['draftRecipeCountkitchen'] = $this->buildQueryBuilder($request)->field('type')->equals(Recipe::$types['kitchen911'])->getQuery()->execute()->count();
        return new JsonResponse($renderingParams);
    }

    public function buildQueryBuilder(Request $request){
        $dm = $this->get('doctrine_mongodb')->getManager();
        $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')->field('deleted')->equals(false)->field('status')->equals($this->recipeStatus);
        if ($request->get('status') && $request->get('status') == 'assigned') {
            $queryBuilder->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()));
        } else {
            if($this->recipeStatus=='new'){
                $queryBuilder->field('assignedTo')->exists(FALSE);

            }
        }

        //search parameters query
        if ($request->get('title')) {
            $queryBuilder->addAnd($queryBuilder->expr()->addOr(
                        $queryBuilder->expr()->field('title')->equals(new \MongoRegex(('/' .  preg_quote($request->get('title')) . '/i')))
                        )->addOr(
                        $queryBuilder->expr()->field('titleEn')->equals(new \MongoRegex(('/' .  preg_quote($request->get('title')) . '/i')))
                        )
                    );
        }
        if ($request->get('chef')) {
            $queryBuilder = $queryBuilder->field('chef.$id')->equals(new \MongoId($request->get('chef')));
        }

        if ($request->get('from') && (bool) strtotime($request->get('from'))) {
            $queryBuilder = $queryBuilder->field('createdAt')->gte(new \DateTime($request->get('from')));
        }
        if ($request->get('to') && (bool) strtotime($request->get('to'))) {
            $fromDate = new \DateTime($request->get('to'));
            $queryBuilder->field('createdAt')->lte($fromDate->modify('+1 day'));
        }
        if ($request->get('tags')) {
            $tags = explode(',', $request->get('tags'));
            $queryBuilder->addAnd($queryBuilder->expr()->addOr(
                        $queryBuilder->expr()->field('recipeTags')->all($tags)
                        )
                    );
        }

        if ($request->get('meals')) {
            $meals = explode(',', $request->get('meals'));
            foreach ($meals as $meal) {
                $queryBuilder->field('meal.'.$meal)->exists(true);
            }
        }

        if ($request->get('ingredients')) {
            $ingredients = explode(',', $request->get('ingredients'));
            foreach ($ingredients as $ingredient) {
                $queryBuilder->field('keyIngredient.'.$ingredient)->exists(true);
            }
        }

        if ($request->get('type')) {
            $queryBuilder->field('type')->in($request->get('type'));
        }

        if ($request->get('products')) {
            $products = explode(',', $request->get('products'));
            $queryBuilder->field('products')->all($products);
        }

        if ($request->get('pub-from') && (bool) strtotime($request->get('pub-from'))) {
            $queryBuilder = $queryBuilder->field('publishedAt')->gte(new \DateTime($request->get('pub-from')));
        }
        if ($request->get('pub-to') && (bool) strtotime($request->get('pub-to'))) {
            $fromDate = new \DateTime($request->get('pub-to'));
            $queryBuilder->field('publishedAt')->lte($fromDate->modify('+1 day'));
        }
        return $queryBuilder;
    }

    public function assignToMeAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ASSIGN') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        $recipeId = $request->get('recipeId');
        $status = $this->get('recipe_operations')->assignToMe($recipeId, $this->recipeStatus);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $type = $request->get('type');

        if ($status == RecipeOperations::$TIME_OUT) {
            if ($type && $type == 'view') {
                $this->addFlash('error', $this->trans('failed operation'));
            }
            return new JsonResponse(array_merge(array('status' => 'error', 'message' => $this->get('translator')->trans('failed operation')), $this->getTabCount()));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_OTHER_USER) {
            if ($type && $type == 'view') {
                $this->addFlash('error', $this->get('translator')->trans('sorry this recipe assign to other user', array(), $this->translationDomain));
            }
            return new JsonResponse(array_merge(array('status' => 'error', 'message' => $this->get('translator')->trans('sorry this recipe assign to other user', array(), $this->translationDomain)), $this->getTabCount()));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_ME) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
            if ($type && $type == 'view') {
                $this->addFlash('success', $successMessage);
            }
            return new JsonResponse(array_merge(array('status' => 'success', 'message' => $successMessage), $this->getTabCount()));
        }
    }

    public function draftAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DRAFT') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        $recipeId = $request->get('recipeId');
        $status = $this->get('recipe_operations')->draft($recipeId,$request->get('status') );
        $dm = $this->get('doctrine_mongodb')->getManager();
        $type = $request->get('type');

        if ($status == RecipeOperations::$TIME_OUT) {
            if ($type && $type == 'view') {
                $this->addFlash('error', $this->trans('failed operation'));
            }
            return new JsonResponse(array_merge(array('status' => 'error', 'message' => $this->get('translator')->trans('failed operation')), $this->getTabCount()));
        } elseif ($status == RecipeOperations::$DRAFT) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
            if ($type && $type == 'view') {
                $this->addFlash('success', $successMessage);
            }
            return new JsonResponse(array_merge(array('status' => 'success', 'message' => $successMessage), $this->getTabCount()));
        }
    }

    public function publishAction(Request $request)
    {
        $type=$request->get('type');
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $securityContext = $this->get('security.authorization_checker');
        $publishOperations = $this->get('recipe_operations');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {
            if($type && $type=='view'){
                $this->addFlash('error', $this->trans('You are not authorized to do this action any more'));
            }
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
                    'goodyStar' => $recipe->getGoodyStar(),
                    'translationDomain' => $this->translationDomain,
                    'locations' => $locations,
                    'currentLocations' => $currentPublishedLocations,
                    'document' => $recipe
            ));
        } else if ($request->getMethod() === 'POST') {

            $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($request->get('documentId'));
            if (!$recipe) {
                if ($type && $type == 'view') {
                    $this->addFlash('error', $this->trans('not done'));
                }
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }
            $locations = $request->get('publishLocation', array());
            if (!empty($locations)) {
                $locations = $dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('id' => array('$in' => $request->get('publishLocation'))));
            }

            $recipeStatus = $recipe->getStatus();
            $status = $request->get('status');
            $goodyStar = $request->get('goodyStar',false);
            if ($status != $recipeStatus) {
                if ($type && $type == 'view') {
                    $this->addFlash('error', $this->trans('not done'));
                }
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }


            switch ($recipeStatus) {
                case 'new':
                case 'draft':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($recipe, $locations, FALSE, $goodyStar);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->autoPublish($recipe, $locations, $autoPublishDate, $goodyStar);
                    }
                    break;
                case 'publish':
                    $publishResult = $publishOperations->managePublishControl($recipe, $locations, $goodyStar);
                    break;
                case 'deleted':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($recipe, $locations, TRUE, $goodyStar);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->autoPublish($recipe, $locations, $autoPublishDate, $goodyStar);
                    }
                    break;
                case 'autopublish':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($recipe, $locations, FALSE, $goodyStar);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->manageAutoPublishControl($recipe, $locations, $autoPublishDate, $goodyStar);
                    }
                    break;
            }

            if ($type && $type == 'view') {
                $this->addFlash($publishResult['status'], $publishResult['message']);
            }
            $this->container->get('facebook_scrape')->update($recipe);


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
        $renderingParams['draftRecipeCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['draft'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        if ($this->listStatus == 'list_assigned_recipe') {
            $renderingParams['draftRecipeCountRecipe'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                    ->field('type')->equals(Recipe::$types['recipe'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountArticle'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                    ->field('type')->equals(Recipe::$types['article'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountTip'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                    ->field('type')->equals(Recipe::$types['tip'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountkitchen'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                    ->field('type')->equals(Recipe::$types['kitchen911'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
        } elseif ($this->listStatus == 'list_new_recipe') {

            $renderingParams['draftRecipeCountRecipe'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['recipe'])
                    ->field('assignedTo')->exists(FALSE)
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountArticle'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['article'])
                    ->field('assignedTo')->exists(FALSE)
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountTip'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['tip'])
                    ->field('assignedTo')->exists(FALSE)
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountkitchen'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('assignedTo')->exists(FALSE)
                    ->field('type')->equals(Recipe::$types['kitchen911'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
        } else {
            $renderingParams['draftRecipeCountRecipe'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['recipe'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountArticle'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['article'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountTip'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['tip'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
            $renderingParams['draftRecipeCountkitchen'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals($this->recipeStatus)
                    ->field('type')->equals(Recipe::$types['kitchen911'])
                    ->field('deleted')->equals(false)
                    ->getQuery()->execute()->count();
        }
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

        );
        $breadCrumbArray = $this->preparedMenu($menus);

        $recipe = new Recipe();
        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('contentType' => 'recipe', 'type' => 'add', 'class' => 'dev-page-main-form dev-js-validation form-horizontal'),'type'=>'create'));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('recipe');

                if($formData['related']){
                    $this->updateRelatedRecipe($recipe, $formData['related'],$dm,'recipe');
                }

//                $tags = $formData['tags'];
//                $tagsEn = $formData['tagsEn'];
//
//                $recipe->setTags();
//                $recipe->setTagsEn();

//                if ($tags) {
//                    $tagsArray = explode(',', $tags);
//                    $tagsArray = array_unique($tagsArray);
//                    foreach ($tagsArray as $tag) {
//                        $tag = trim($tag);
//                        if (mb_strlen($tag, 'UTF-8') <= 330) {
//                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('tag' => $tag));
//                            if (!$tagObject) {
//                                $NewTag = new Tag();
//                                $NewTag->setName($tag);
//                                $NewTag->setTag($tag);
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
//                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('tagEn' => $tag));
//                            if (!$tagObject) {
//                                $NewTag = new Tag();
//                                $NewTag->setName($tag);
//                                $NewTag->setTagEn($tag);
//                                $dm->persist($NewTag);
//                                $recipe->addTagEn($NewTag);
//                            } else {
//                                $recipe->addTagEn($tagObject);
//                            }
//                        }
//                    }
//                }
                $dm->persist($recipe);
                $this->slugifier($recipe);
                $this->get('history_logger')->log($recipe, History::$ADD);

                $dm->flush();

                $this->updateMaterialGallary($recipe, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_recipenew_list_new_recipe'), array(), true));

            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Recipe:create.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'title' => $this->trans('Add new Recipe', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

    public function slugifier($recipe)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($recipe->getTitle(), 100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($recipe->getTitleEn(), 100));

        $arabicCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('deleted')->equals(FALSE)
                ->field('slug')->equals($slugAr)
                ->field('id')->notEqual($recipe->getId())->
                getQuery()->execute()->count();

        $englishCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('deleted')->equals(FALSE)
                ->field('slugEn')->equals($slugEn)
                ->field('id')->notEqual($recipe->getId())->
                getQuery()->execute()->count();
        if ($arabicCount != 0) {
            $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($recipe->getTitle(), 100) . "-" . date('ymdHis'));
        }
        if ($englishCount != 0) {
            $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($recipe->getTitleEn(), 100) . "-" . date('ymdHis'));
        }
        $recipe->setSlug($slugAr);
        $recipe->setSlugEn($slugEn);

        $type = strtoupper('type_' . $recipe->getType());

        $slug = new Slug();
        $slug->setReferenceId($recipe->getId());
        $slug->setType(Slug::$$type);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);
        $dm->flush();
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction(Request $request,$id) {

        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Recipe', 'link' => $this->generateUrl('ibtikar_glance_dashboard_recipe_create')),
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();
        if ($id) {
            $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($id);
            if (!$recipe || $this->recipeStatus != $recipe->getStatus() ) {
                throw $this->createNotFoundException($this->trans('Wrong id'));
            }
        }

        $contentType =  $recipe->getType() == Recipe::$types['recipe'] ? 'Recipe' : 'Blog';

//        $tagSelected = $this->getTagsForDocument($recipe);
//        $tagSelectedEn = $this->getTagsForDocument($recipe, "en");

        $form = $this->createForm(RecipeType::class, $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('contentType' => strtolower($contentType), 'type' => 'edit', 'class' => 'dev-page-main-form dev-js-validation form-horizontal')));
//
//        $form->get('tags')->setData($tagSelected);
//        $form->get('tagsEn')->setData($tagSelectedEn);


        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $formData = $request->get('recipe');

                if ($contentType == 'Recipe' && $formData['related']) {
                    $this->updateRelatedRecipe($recipe, $formData['related'], $dm,'recipe');
                }

                if($contentType == 'Blog' && $formData['related_article']){
                    $this->updateRelatedRecipe($recipe, $formData['related_article'],$dm,'article');
                }

                if($contentType == 'Blog' && $formData['related_tip']){
                    $this->updateRelatedRecipe($recipe, $formData['related_tip'],$dm,'tip');
                }

                $this->updateMaterialGallary($recipe, $formData['media'], $dm);

//                $tags = $formData['tags'];
//                $tagsEn = $formData['tagsEn'];
//
//                $recipe->setTags();
//                $recipe->setTagsEn();

//                if ($tags) {
//                    $tagsArray = explode(',', $tags);
//                    $tagsArray = array_unique($tagsArray);
//                    foreach ($tagsArray as $tag) {
//                        $tag = trim($tag);
//                        if (mb_strlen($tag, 'UTF-8') <= 330) {
//                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('tag' => $tag));
//                            if (!$tagObject) {
//                                $NewTag = new Tag();
//                                $NewTag->setName($tag);
//                                $NewTag->setTag($tag);
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
//                            $tagObject = $dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findOneBy(array('tagEn' => $tag));
//                            if (!$tagObject) {
//                                $NewTag = new Tag();
//                                $NewTag->setName($tag);
//                                $NewTag->setTagEn($tag);
//                                $dm->persist($NewTag);
//                                $recipe->addTagEn($NewTag);
//                            } else {
//                                $recipe->addTagEn($tagObject);
//                            }
//                        }
//                    }
//                }
                $this->get('history_logger')->log($recipe, History::$EDIT);

                $dm->flush();

                $this->container->get('facebook_scrape')->update($recipe);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_' . strtolower($this->calledClassName) . '_list_' . $recipe->getStatus() . '_recipe'), array(), true));
            }  else {
//                $errors=array();
//                   foreach ($form->getErrors() as $key => $error) {
//            $errors[] = $error->getMessage();
//        }
//                 \Doctrine\Common\Util\Debug::dump($errors);
//                 exit;
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:'.$contentType.':edit.html.twig', array(
                    'recipe' => $recipe,
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'title' => $this->trans('Edit '.$recipe->getType(), array(), $this->translationDomain),
                    'translationDomain' => $this->translationDomain,
                    'room' => $this->calledClassName,
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

    public function deleteAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        $type=$request->get('type');

        if (!$securityContext->isGranted('ROLE_ADMIN') && !$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE')) {
            if($type && $type=='view'){
                $this->addFlash('error', $this->trans('You are not authorized to do this action any more'));

            }
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        if ($request->getMethod() === 'GET') {
            $id = $request->get('id', '');
            if ($id) {
                $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($id);
                if (!$recipe) {
                    if ($type && $type == 'view') {
                        $this->addFlash('error', $this->trans('You are not authorized to do this action any more'));
                    }
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

            if ($type && $type == 'view') {
                $this->addFlash($forwardResult['status'], $forwardResult['message']);
            }

            return new JsonResponse(array_merge(array('status' => $forwardResult["status"], 'message' => $forwardResult["message"]), $this->getTabCount()));
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
        $message = str_replace(array('%action%', '%item-translation%', '%ids-count%'), array($translator->trans($bulkAction), $this->trans($request->get('type','recipe'),array(),$this->translationDomain), count($ids)), $translator->trans('successfully %action% %success-count% %item-translation% from %ids-count%.'));
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
                                $actionTd.= '<a class="btn btn-default"  href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_edit', array('id' => $document->getId())) . '" ><i class="icon-pencil" data-popup="tooltip" title="' . $this->trans('Edit ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-placement="right"></i></a>';
                            } elseif ($action == 'Delete' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE')) && !$document->getNotModified()) {
                                $actionTd.= '<a class="btn btn-default dev-delete-single-recipe"  data-href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_delete', array('id' => $document->getId())) . '" data-id="'. $document->getId().'" ><i class="icon-trash" data-popup="tooltip" title="' . $this->trans('Delete ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-placement="right"></i></a>';
                            } elseif ($action == 'ViewOne' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_VIEWONE'))) {
                                $actionTd.= '<a class="btn btn-default"  href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_view', array('id' => $document->getId())) . '" ><i class="icon-eye" data-popup="tooltip"  title="' . $this->trans('View One ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"  data-placement="right" ></i></a>';
                            } elseif ($action == 'Assign' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ASSIGN'))) {
                                $actionTd.= '<a class="btn btn-default dev-assign-to-me" href="javascript:void(0);"  data-url="'.$this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_assign_to_me').'" data-id="'.$document->getId().'"><i class="icon-user"  title="' . $this->trans('AssignToMe', array(), $this->translationDomain) . '"  data-popup="tooltip" data-placement="right"></i></a>';
                            }
                            elseif ($action == 'Publish' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH'))) {
                                $actionTd.= '<a href="javascript:void(0)" data-toggle="modal"  class="btn btn-default dev-publish-recipe" data-id="'.$document->getId().'"><i class="icon-share" data-placement="right"  data-popup="tooltip" title="' . $this->trans('publish ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"></i></a>
';
                            }
                            elseif ($action == 'draft' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DRAFT'))) {
                                $actionTd.= '<a   href="javascript:void(0);"   class="btn btn-default dev-recipes-draft"  data-status="'.$document->getStatus().'"  data-id="'.$document->getId().'" data-url="'.$this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_draft').'"><i class="icon-file-text" data-popup="tooltip"  data-placement="right" title="' . $this->trans('draft', array(), $this->translationDomain) . '"></i></a>';
                            }
//                            elseif ($action == 'AutoPublish' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_AUTOPUBLISH'))) {
//                                $actionTd.= '<a class="btn btn-default" href="javascript:void(0);" title="'  . $this->trans('autopublish ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-popup="tooltip"  data-placement="bottom" ><i class="icon-checkmark3"></i></a>';
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

    public function relatedMaterialDeleteAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $parent = $request->get('parent');
        $child = $request->get('child');

        $materialParent = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($parent);
        $materialChild = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($child);


        $contentType = $materialChild->getType();
        $removeMethod = "removeRelated".strtoupper($contentType);
        $getMethod = "getRelated".strtoupper($contentType);

        if($materialParent && $materialChild && $materialParent->$getMethod()->contains($materialChild)){
            $materialParent->$removeMethod($materialChild);
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

    public function relatedMaterialAddAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $parent = $request->get('parent');
        $child = $request->get('child');

        $materialParent = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($parent);
        $materialChild = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($child);


        $contentType = $materialChild->getType();
        $addMethod = "addRelated" . ucfirst($contentType);
        $getMethod = "getRelated" . ucfirst($contentType);
        if ($this->validToRelate($materialChild, $materialParent) && count($materialParent->$getMethod()) < 10) {

            $materialParent->$addMethod($materialChild);
            $dm->flush();
        }
        $response = array('status' => 'success', 'message' => $this->trans('done sucessfully'));


        return new JsonResponse($response);
    }

    public function viewAction(Request $request, $id) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $recipe = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('id')->equals($id)
                    ->field('status')->equals($this->recipeStatus)
                    ->field('chef')->prime(true)
                    ->field('tags')->prime(true)
                    ->field('tagsEn')->prime(true)
                    ->field('relatedRecipe')->prime(true)

                    ->getQuery()->getSingleResult();

                if (!$recipe) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $data['document'] = $recipe;


        $data['mediaList'] = array();
        $data['relatedRecipes'] = array();
        $data['relatedArticles'] = array();
        $data['relatedTips'] = array();



        foreach ($recipe->getRelatedRecipe() as $relatedRecipe) {
            $data['relatedRecipes'][] = array(
                'title' => $relatedRecipe->getTitle(),
                'titleEn' => $relatedRecipe->getTitleEn(),
                'img' => $relatedRecipe->getCoverPhoto() ? $relatedRecipe->getCoverPhoto()->getType() == 'image' ? '/' . $relatedRecipe->getCoverPhoto()->getWebPath():'https://i.ytimg.com/vi/' . $relatedRecipe->getCoverPhoto()->getVid().'/hqdefault.jpg' : '',
                'url' =>  $this->generateUrl('ibtikar_goody_frontend_'.trim($relatedRecipe->getType()).'_view', array('slug' => $relatedRecipe->getSlug()), true)

            );
        }

        foreach ($recipe->getRelatedArticle() as $relatedArticle) {
            $data['relatedArticles'][] = array(
                'title' => $relatedArticle->getTitle(),
                'titleEn' => $relatedArticle->getTitleEn(),
                'img' => $relatedArticle->getCoverPhoto() ? $relatedArticle->getCoverPhoto()->getType() == 'image' ? '/' . $relatedArticle->getCoverPhoto()->getWebPath():'https://i.ytimg.com/vi/' . $relatedArticle->getCoverPhoto()->getVid().'/hqdefault.jpg' : '',

                'url' =>  $this->generateUrl('ibtikar_goody_frontend_'.trim($relatedArticle->getType()).'_view', array('slug' => $relatedArticle->getSlug()), true)

            );
        }


        foreach ($recipe->getRelatedTip() as $relatedTip) {
            $data['relatedTips'][] = array(
                'title' => $relatedTip->getTitle(),
                'titleEn' => $relatedTip->getTitleEn(),
                'img' => $relatedTip->getCoverPhoto() ? $relatedTip->getCoverPhoto()->getType() == 'image' ? '/' . $relatedTip->getCoverPhoto()->getWebPath():'https://i.ytimg.com/vi/' . $relatedTip->getCoverPhoto()->getVid().'/hqdefault.jpg' : '',

                'url' =>  $this->generateUrl('ibtikar_goody_frontend_'.trim($relatedTip->getType()).'_view', array('slug' => $relatedTip->getSlug()), true)

            );
        }


        $mediaRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Media');
        $medias = $mediaRepo->getRecipeMedia($recipe->getId());
        foreach ($medias as $media) {
            if ($media->getCoverPhoto()) {
                $data['coverPhoto']['type'] = $media->getType();
//                $data['coverPhoto']['img'] = $media->getType()=='image'? $document->getMigrated()?$media->getWebPath():$this->getImageUrl($media->getWebPath()): 'https://www.youtube.com/embed/' . $media->getVid() . '?autoplay=1';
                $data['coverPhoto']['img'] = $media->getType() == 'image' ? '/'.$media->getWebPath() : 'https://i.ytimg.com/vi/' . $media->getVid().'/hqdefault.jpg' ;
                $data['coverPhoto']['caption'] = $media->getCaptionAr();
                $data['coverPhoto']['captionEn'] = $media->getCaptionEn();
                continue;
            }
            if ($media->getType() == 'image') {

                $data['mediaList'] [] = array(
                    'type' => $media->getType(),
                    'img' => '/'.$media->getWebPath(),
                    'caption' =>  $media->getCaptionAr(),
                    'captionEn' =>  $media->getCaptionEn(),
                );
            } else {

                $data['mediaList'] [] = array(
                    'type' => $media->getType(),
                    'videoCode' => $media->getVid(),
                    'caption' =>  $media->getCaptionAr(),
                    'captionEn' =>  $media->getCaptionEn() ,
                );
            }
        }





        return $this->render('IbtikarGlanceDashboardBundle:Recipe:view.html.twig', array(
                    'translationDomain' => $this->translationDomain,
                    'data' => $data,

        ));
    }
    
    public function HistoryAction(Request $request, $offset=0,$limit=30) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        if(!is_numeric($offset)){
         $offset=0;   
        }
        if(!is_numeric($limit)){
          $limit=30;  
        }
        $dm->getFilterCollection()->disable('soft_delete');

        $history = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:History')
                        ->skip($offset)->limit($limit)
                        ->sort('createdAt', 'ASC')
                        ->getQuery()->execute();




     

        return $this->render('IbtikarGlanceDashboardBundle:Recipe:history.html.twig', array(
                    'translationDomain' => $this->translationDomain,
                    'history' => $history,

        ));
    }

    /**
     * Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function chefStatisticsAction(Request $request) {
        $requestedDates = $this->getRequiredFromToDatesOrInvalidResponseFromCurrentRequest($request);
        if ($requestedDates instanceof JsonResponse) {
            return $requestedDates;
        }
        $responseData = array('status' => 'success', 'code' => 200, 'chefs' => array());
        $dm = $this->get('doctrine_mongodb')->getManager();
        $recipeRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe');
        $chefsData = $recipeRepo->createQueryBuilder()
                        ->field('publishedAt')->gte($requestedDates['from'])
                        ->field('publishedAt')->lte($requestedDates['to'])
                        ->field('type')->in(array(Recipe::$types['recipe'], Recipe::$types['article']))
                        ->distinct('chef')
                        ->getQuery()->execute();
        $chefsIds = array();
        foreach ($chefsData as $chefData) {
            $chefsIds [] = $chefData['$id'];
        }
        if (count($chefsIds) > 0) {
            $chefs = $dm->getRepository('IbtikarGlanceUMSBundle:Staff')->findBy(array('id' => array('$in' => $chefsIds)));
            foreach ($chefs as $chef) {
                $responseData['chefs'] [] = array(
                    'name' => $chef->__toString(),
                    'publishedRecipesCount' => $recipeRepo->createQueryBuilder()
                            ->field('chef')->references($chef)
                            ->field('publishedAt')->gte($requestedDates['from'])
                            ->field('publishedAt')->lte($requestedDates['to'])
                            ->field('type')->equals(Recipe::$types['recipe'])
                            ->getQuery()->count(),
                    'publishedArticlesCount' => $recipeRepo->createQueryBuilder()
                            ->field('chef')->references($chef)
                            ->field('publishedAt')->gte($requestedDates['from'])
                            ->field('publishedAt')->lte($requestedDates['to'])
                            ->field('type')->equals(Recipe::$types['article'])
                            ->getQuery()->count()
                );
            }
        }
        return new JsonResponse($responseData);
    }

    /**
     * Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function recipeStatisticsAction(Request $request) {
        $requestedDates = $this->getRequiredFromToDatesOrInvalidResponseFromCurrentRequest($request);
        if ($requestedDates instanceof JsonResponse) {
            return $requestedDates;
        }
        $type = trim($request->get('type'));
        if (!$type) {
            return new JsonResponse(array('status' => 'error', 'message' => 'missing type parameter.'));
        }
        if (!in_array($type, Recipe::$types)) {
            return new JsonResponse(array('status' => 'error', 'message' => 'invalid type.'));
        }
        $responseData = array('status' => 'success', 'code' => 200, 'counts' => array());
        $recipeRepo = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Recipe');
        $responseData['counts']['publishedCount'] = $recipeRepo->createQueryBuilder()
                        ->field('publishedAt')->gte($requestedDates['from'])
                        ->field('publishedAt')->lte($requestedDates['to'])
                        ->field('status')->equals(Recipe::$statuses['publish'])
                        ->field('type')->equals($type)
                        ->getQuery()->count();
        $responseData['counts']['deletedCount'] = $recipeRepo->createQueryBuilder()
                        ->field('deletedAt')->gte($requestedDates['from'])
                        ->field('deletedAt')->lte($requestedDates['to'])
                        ->field('status')->equals(Recipe::$statuses['deleted'])
                        ->field('type')->equals($type)
                        ->getQuery()->count();
        $responseData['counts']['newCount'] = $recipeRepo->createQueryBuilder()
                        ->field('createdAt')->gte($requestedDates['from'])
                        ->field('createdAt')->lte($requestedDates['to'])
                        ->field('type')->equals($type)
                        ->getQuery()->count();
        return new JsonResponse($responseData);
    }

}
