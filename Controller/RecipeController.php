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
                            ->field('status')->equals('new')
                            ->field('assignedTo')->exists(FALSE)
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Assign', 'ViewOne'));
        } else if ($this->listStatus == 'list_assigned_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('assignedTo.$id')->equals(new \MongoId($this->getUser()->getId()))
                    ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', "AutoPublish", 'ViewOne'));
            $this->listViewOptions->setBulkActions(array("Delete"));
        } else if ($this->listStatus == 'list_autopublish_recipe') {
            $queryBuilder = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals('autopublish')
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
        $recipeId = $request->get('recipeId');
        $status = $this->get('recipe_operations')->assignToMe($recipeId);
        if ($status == RecipeOperations::$TIME_OUT) {

//            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));

            return new JsonResponse(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation')));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_OTHER_USER) {
//        $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));
            return new JsonResponse(array('status' => 'failedAlert', 'message' => $this->get('translator')->trans('sorry this recipe assign to other user')));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_ME) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
//            $this->get('session')->getFlashBag()->add('success', $successMessage);
            return new JsonResponse(array('status' => 'success', 'message' => $successMessage));
        }
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

        $form = $this->createForm("Ibtikar\GlanceDashboardBundle\Form\Type\RecipeType", $recipe, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

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
