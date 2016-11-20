<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\base;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\StaffListColumns;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author Moemen Hussein <momen.shaaban@ibtikar.net.sa>
 */
class RoomController extends BackendController {

    protected $roomName = 'news';
    protected $roomType = 'Recipe';
    protected $listName;
    protected $listStatus;
    protected $sublistName = 'News';

    public function __construct() {
        parent::__construct();
        if ($this->calledClassName != 'Dashboard') {
            $calledClassName = explode('\\', $this->calledClassName);
            $this->calledClassName = 'Room_' . $calledClassName[1];
        }
        $this->translationDomain = 'room';
    }

    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array("searchFieldType" => "input"),
            "titleEn" => array("type" => "translated"),
            "country" => array("isSortable" => false),
//            "city" => array("isSortable" => false),
            "createdAt" => array("type" => "date"),
            "chef" => array("isSortable" => false),
        );
        $this->defaultListColumns = array(
            "title",
            "titleEn",
            "createdAt",
            "chef",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");


    }

    protected function configureListParameters(Request $request) {
        $dm=$this->get('doctrine_mongodb')->getManager();
        if ($this->listStatus == 'list_new_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                            ->field('room')->equals($this->roomName)
//                            ->field('status')->equals('new')
//                            ->field('assignedTo')->equals(null)
                            ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Assign', 'Show'));
        } else if ($this->listStatus == 'list_assigned_recipe') {
            $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                            ->field('room')->equals($this->roomName)
                            ->field('assignedTo')->equals($this->getUser()->getId())
                            ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'AutoPublish', 'Forward', 'Backward', 'Show', "Search"));
            $this->listViewOptions->setBulkActions(array("Forward", "Delete"));
        }  else if ($this->listStatus == 'list_autopublish_recipe') {
            $queryBuilder = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                            ->field('room')->equals($this->roomName)
                            ->field('status')->equals('autopublish')
                            ->field('assignedTo')->equals(null)
                            ->field('deleted')->equals(false);
            $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', "AutoPublishControl", 'Forward', 'Show', "Search"));
            $this->listViewOptions->setBulkActions(array("Forward", "Delete"));
            $this->listViewOptions->setDefaultSortBy("autoPublishDate");
            $this->listViewOptions->setDefaultSortOrder("desc");
        }

        if (isset($queryBuilder))
            $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:List:recipeList.html.twig");
    }

    public function listNewRecipeAction(Request $request) {
        $this->listStatus = 'list_new_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::listAction($request);
    }

    public function listAssignedRecipeAction(Request $request) {
        $this->listStatus = 'list_assigned_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::listAction($request);
    }

    public function listautopublishRecipeAction(Request $request) {
        $this->listStatus = 'list_autopublish_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::listAction($request);
    }


    public function changeListNewRecipeColumnsAction(Request $request) {
        $this->listStatus = 'list_new_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    public function changeListAssignedRecipeColumnsAction(Request $request) {
        $this->listStatus = 'list_assigned_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    public function changeListAutopublishRecipeColumnsAction(Request $request) {
        $this->listStatus = 'list_autopublish_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }




    protected function doList(Request $request) {
        $renderingParams = parent::doList($request);
//        $renderingParams['newRecipeCount'] = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                        ->field('room')->equals($this->roomName)
//                        ->field('status')->equals('new')
//                        ->field('assignedTo')->equals(null)
//                        ->field('deleted')->equals(false)
//                        ->getQuery()->execute()->count();
//        $renderingParams['assignedRecipeCount'] = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                        ->field('room')->equals($this->roomName)
//                        ->field('assignedTo')->equals($this->getUser()->getId())
//                        ->field('deleted')->equals(false)
//                        ->getQuery()->execute()->count();
//        $renderingParams['backwardRecipeCount'] = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                        ->field('room')->equals($this->roomName)
//                        ->field('status')->equals('backward')
//                        ->field('assignedTo')->equals(null)
//                        ->field('deleted')->equals(false)
//                        ->getQuery()->execute()->count();
//
//        $renderingParams['autopublishRecipeCount'] = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                ->field('room')->equals($this->roomName)
//                ->field('status')->equals('autopublish')
//                ->field('assignedTo')->equals(null)
//                ->field('deleted')->equals(false)
//                ->getQuery()->execute()->count();
//        $renderingParams['roomName'] = $this->roomName;
//        $renderingParams['roomType'] = $this->roomType;


        return $renderingParams;
    }


    /**
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return type
     * @author Maisara khedr
     */
    public function assignAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $usersPermissionsData = $request->get("data", array());

            $status = 'success';
            foreach ($usersPermissionsData as $record) {
                $userId = $record['id'];
                $permissions = isset($record['permissions']) ? $record['permissions'] : array();
                $dm = $this->get('doctrine_mongodb')->getManager();
                $user = $dm->getRepository('IbtikarBackendBundle:Staff')->findOneBy(array('id' => $userId, 'deleted' => false));
                if ($user) {
                    $user->setRoomPermissions($this->roomName, $permissions);
                    $dm->flush();
                } else {
                    $status = 'failed';
                }
            }
            if ($status == 'success') {
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));
            } else {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));
            }
        } else {

            $breadcrumbs = $this->get("white_october_breadcrumbs");
            $breadcrumbs->addItem('backend-home', $this->generateUrl('backend_home'));
            if ($this->roomName == 'archive') {
                $breadcrumbs->addItem('List ' . ucfirst($this->roomName) . 'Room', $this->generateUrl('room_' . $this->roomName . '_list_deleted_recipe'));
            } else if ($this->roomName == 'published') {
                $breadcrumbs->addItem('List ' . ucfirst($this->roomName) . 'Room', $this->generateUrl('room_' . $this->roomName . '_list'));
            } else {
                $breadcrumbs->addItem('List ' . ucfirst($this->roomName) . 'Room', $this->generateUrl('room_' . $this->roomName . '_list_new_recipe'));
            }
            $breadcrumbs->addItem('Manage Room Users', $this->generateUrl('room_' . $this->roomName . '_assign'));
        }
        $permissions = $this->getRoomPermissionsArray($this->container->getParameter('permissions'), $this->roomName);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $users = $dm->getRepository('IbtikarBackendBundle:Staff')->getRoomUsers($this->roomName);
        $allUsers = $dm->getRepository('IbtikarBackendBundle:Staff')->getStaffExceptAdmins($this->roomName);



        return $this->render('IbtikarBackendBundle:rooms:assign.html.twig', array(
                    'translationDomain' => $this->translationDomain,
                    'permissions' => $permissions,
                    'roomName' => $this->roomName,
                    'routeName' => $this->roomName,
                    'users' => $users,
                    'allUsers' => $allUsers
        ));
    }







    public function assignToMeAction(Request $request) {
        $type = $request->get('type');
        $recipeId = $request->get('recipeId');
        $status = $this->get('recipe_operations')->assignToMe($recipeId, $this->roomName, $type);
        if ($status == RecipeOperations::$TIME_OUT) {

            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));

            return new JsonResponse(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation'), 'type' => $type));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_OTHER_USER) {
//        $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));
            return new JsonResponse(array('status' => 'failedAlert', 'message' => $this->get('translator')->trans('sorry this recipe assign to other user'), 'type' => $type));
        } elseif ($status == RecipeOperations::$ASSIGN_TO_ME) {
            $successMessage = $this->get('translator')->trans('done sucessfully');
            $this->get('session')->getFlashBag()->add('success', $successMessage);
            return new JsonResponse(array('status' => 'success', 'message' => $successMessage, 'type' => $type));
        }
    }


}
