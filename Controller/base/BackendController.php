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

class BackendController extends Controller {

    protected $translationDomain = '';
    protected $listStatus = '';
    protected $dashboardRoom = '';
    protected $sublistName;
    protected $listName;
    protected $calledClassName = '';
    protected $roomType = '';
    public $oneItem = '';

    /**
     * @var \Ibtikar\BackendBundle\Service\ListView service object
     */
    protected $listViewOptions;

    /**
     * list of all available columns in a list
     * Available Options ([type = "string","date","translated","computed"][isSortable = true,false][tooltip=null,"property value to be displayed"][class="","any class to be added to the th and td"])
     * @var array
     */
    protected $allListColumns;
    protected $defaultListColumns;

    public function __construct() {
        if ($this->calledClassName == '') {
            $calledClassPath = get_called_class();
            $calledClassName = substr($calledClassPath, strrpos($calledClassPath, "Controller\\") + strlen("Controller\\"));
            $this->calledClassName = substr($calledClassName, 0, strlen($calledClassName) - strlen("Controller"));
        }
        if (!$this->translationDomain) {
            $this->translationDomain = strtolower($this->calledClassName);
        }
    }

    public function listAction(Request $request) {
        if ($this->calledClassName == 'Room_NotStarted') {
            $this->calledClassName = 'NotStarted';
        }
        if ($this->calledClassName == 'Room_Done') {
            $this->calledClassName = 'Done';
        }
        if ($this->calledClassName == "Room_InProgress") {
            $this->calledClassName = 'InProgress';
        }
        $this->listViewOptions = $this->get("list_view");
        $this->listViewOptions->setListType("list");
        $renderingParams = $this->doList($request);
        if ($this->listViewOptions->getTemplate()) {
            return $this->render($this->listViewOptions->getTemplate(), $renderingParams);
        } else {
            return $this->render('IbtikarGlanceUMSBundle:List:baseList.html.twig', $renderingParams);
        }
    }

    public function trashAction(Request $request) {
        $this->listViewOptions = $this->get("list_view");
        $this->listViewOptions->setListType("trash");
        $renderingParams = $this->doList($request);
        return $this->render('IbtikarGlanceUMSBundle:List:baseList.html.twig', $renderingParams);
    }

    protected function getCurrentColumns($listName) {
        /* Get List Fields */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository('IbtikarBackendBundle:StaffListColumns')->findOneBy(array('staff' => $this->getUser()->getId(), "listName" => $listName));
        if ($document && $document->getColumns()) {
            $selectedColumns = explode(",", $document->getColumns());
        } else {
            $selectedColumns = $this->defaultListColumns;
        }
        return $selectedColumns;
    }

    protected function doList(Request $request) {
        $this->prepareListParameters();
        if ($this->listViewOptions->getListType() == "list") {
            $this->configureListParameters($request);
            $this->configureListColumns();
        }
        if ($this->listViewOptions->getListType() == "trash") {
            $this->configureTrashParameters($request);
            $this->configureTrashColumns();
        }

        if ($this->listName) {
            $listName = $this->listName;
        } else {
            $listName = strtolower($this->calledClassName) . '_' . $this->listViewOptions->getListType();
        }

        $selectedColumns = $this->getCurrentColumns($listName);

        foreach ($selectedColumns as $column) {
            // check if the column was removed from the main list columns
            if (isset($this->allListColumns[$column])) {
                $fieldOptions = $this->allListColumns[$column];
                $defaultOptions = array(
                    "type" => "string",
                    "document" => "document",
                    "isSortable" => true,
                    "isClickable" => false,
                    "tooltip" => null,
                    "getterArguments" => null,
                    "class" => "",
                    "searchFieldType" => "input",
                    "sortOrderType" => "normal"
                );
                $fieldOptions = array_merge($defaultOptions, $fieldOptions);
                $this->listViewOptions->addField($this->get("string_utilities")->humanize($column), $column, $fieldOptions["type"], $fieldOptions["sortOrderType"], $fieldOptions["tooltip"], $fieldOptions["getterArguments"], $fieldOptions["isSortable"], $fieldOptions["isClickable"], $fieldOptions["class"], $fieldOptions["document"]);
            }
        }

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        foreach ($this->listViewOptions->getBreadcrumbs() as $title => $route) {
            $breadcrumbs->addItem($title, $route);
        }

        $sortBy = $this->listViewOptions->getDefaultSortBy();
        $sortOrder = $this->listViewOptions->getDefaultSortOrder();

        $queryBuilder = $this->listViewOptions->getListQueryBuilder();

        if (is_null($request->get('sort')) || !in_array($request->get('sort'), $selectedColumns)) {
            $queryBuilder = $queryBuilder->sort($sortBy, $sortOrder);
        }

        $query = $queryBuilder->getQuery();
        $limit = $request->get('limit');
        if (!$limit || !in_array($limit, array(10, 20, 50))) {
            $limit = $this->container->getParameter('per_page_items');
        }

        $pageNumber = $this->get('request')->query->get('page', 1);
        if ($pageNumber < 1) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $pageNumber /* page number */, $limit/* limit per page */
        );

        $items = $pagination->getItems();
        if (!$request->isXmlHttpRequest() && empty($items) && $pagination->getCurrentPageNumber() != 1) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        if ($request->isXmlHttpRequest() && empty($items) && $pagination->getCurrentPageNumber() != 1) {
            $pageNumber = $pageNumber - 1;
            $pagination = $paginator->paginate($query, $pageNumber, $limit);
        }

        if (is_null($request->get('sort')) || !in_array($request->get('sort'), $selectedColumns)) {
            $pagination->setParam('sort', $sortBy);
            $pagination->setParam('direction', $sortOrder);
        }
        if ($this->listName) {
            $changeListColumnType = '_' . $this->listStatus;
        } else {
            $changeListColumnType = '';
        }
        $sublistName = $this->calledClassName;
        if ($this->sublistName) {
            $sublistName = $this->sublistName;
        }
        $renderningParams = array(
            'sublistName' => $sublistName,
            'pageNumber' => $pageNumber,
            'pagination' => $pagination,
            'paginationData' => $pagination->getPaginationData(),
            'translationDomain' => $this->translationDomain,
            'listName' => $this->calledClassName,
            'list' => $this->listViewOptions,
            'changeListColumnType' => $changeListColumnType
        );

        return $renderningParams;
    }

    protected function configureListParameters(Request $request) {

    }

    protected function configureTrashParameters(Request $request) {

    }

    private function prepareListParameters() {
        $this->listViewOptions->setListQueryBuilder($this->createQueryBuilder());
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        if ($this->listStatus != '') {
            $breadcrumbs = array(
                "backend-home" => $this->generateUrl('backend_home'),
                "List " . $this->calledClassName => $this->generateUrl(strtolower($this->calledClassName) . '_' . $this->listStatus)
            );
        } else {
            if ($this->dashboardRoom != '') {
                $breadcrumbs = array(
                    "backend-home" => $this->generateUrl('backend_home'),
                    "List " . $this->calledClassName => $this->generateUrl(strtolower($this->calledClassName) . '_list', array('dashboardRoom' => $this->dashboardRoom))
                );
            } else {
                $breadcrumbs = array(
                    "backend-home" => $this->generateUrl('backend_home'),
                    "List " . $this->calledClassName => $this->generateUrl(strtolower($this->calledClassName) . '_list')
                );
            }
        }
        $this->listViewOptions->setBreadcrumbs($breadcrumbs);
    }

    protected function createQueryBuilder($documentBundle = "IbtikarBackendBundle") {
        try {
            return $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($documentBundle . ':' . $this->calledClassName);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setCalledClassName($calledClassName) {
        $this->calledClassName = $calledClassName;
    }

    public function getCalledClassName() {
        return $this->calledClassName;
    }

    public function getRedirectJsonResponseForRoute($route) {
        return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl($route)), 302);
    }

    public function getLoginResponse() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse(array('status' => 'login'), 401);
        }
        $request = $this->getRequest();
        $request->getSession()->set('redirectUrl', $request->getRequestUri());
        return $this->redirect($this->generateUrl('login'));
    }

    public function getAccessDeniedResponse() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse(array('status' => 'denied'), 403);
        }
        $this->createAccessDeniedExceptionForUser();
    }

    protected function getNotificationResponse($message = null, array $data = array(), $type = null, $hideAfterSeconds = 10) {
        if (!$type) {
            $type = 'success';
        }
        if (!$message && $type === 'success') {
            $message = $this->trans('done sucessfully');
        }
        if (!$message && $type === 'error') {
            $message = $this->trans('failed operation');
        }
        return new JsonResponse(array('status' => 'notification', 'type' => $type, 'message' => $message, 'data' => $data, 'hideAfterSeconds' => $hideAfterSeconds));
    }

    protected function getFailedResponse($status = 'failed') {
        return new JsonResponse(array('status' => $status, 'message' => $this->get('translator')->trans('failed operation')));
    }

    protected function getFailedAlertResponse($errorMessage = '') {
        if (!$errorMessage) {
            $errorMessage = $this->trans('failed operation');
        }
        return new JsonResponse(array('status' => 'failedAlert', 'message' => $errorMessage));
    }

    public function createAccessDeniedExceptionForUser($message = null) {
        throw new AccessDeniedException($message);
    }

    protected function validateDeactivate(Document $document, $status) {
        if (!$document->getEnabled() && $status === 'false') {
            return $this->trans('Already deactivated');
        }
    }

    protected function getObjectShortName() {

        return 'IbtikarGlanceDashboardBundle:' . $this->calledClassName;
    }

    public function trans($string, $param = array(), $translationDomain = null) {
        if (is_null($translationDomain)) {
            $translationDomain = $this->translationDomain;
        }
        return $this->get('translator')->trans($string, $param, $translationDomain);
    }

    public function changeListColumnsAction(Request $request) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $viewRole = ($this->roomType == "Comments") ? 'ROLE_ROOM_COMMENT_' . strtoupper($this->roomIndex) . '_VIEW' : 'ROLE_' . strtoupper($this->calledClassName) . '_VIEW';


        if (!$securityContext->isGranted($viewRole) && !$securityContext->isGranted('ROLE_ADMIN') && !$this->calledClassName = 'Task') {

            return new JsonResponse(array('status' => 'denied'));
        }
        $this->listViewOptions = $this->get("list_view");

        if ($request->get("listType") == "list") {
            $this->listViewOptions->setListType("list");
            $this->configureListColumns();
        }
        if ($request->get("listType") == "trash") {
            $this->listViewOptions->setListType("trash");
            $this->configureTrashColumns();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        if ($this->listName) {
            $staffListColumns = $dm->getRepository('IbtikarBackendBundle:StaffListColumns')->findOneBy(array('staff' => $this->getUser()->getId(), "listName" => $this->listName));
        } else {
            $staffListColumns = $dm->getRepository('IbtikarBackendBundle:StaffListColumns')->findOneBy(array('staff' => $this->getUser()->getId(), "listName" => strtolower($this->calledClassName) . "_" . $this->listViewOptions->getListType()));
        }
        if ($request->getMethod() === 'GET') {
            if ($staffListColumns && $staffListColumns->getColumns()) {
                $selectedColumns = explode(",", $staffListColumns->getColumns());
            } else {
                $selectedColumns = $this->defaultListColumns;
            }

            if (isset($this->roomName) && $this->roomType != "Comments" && $this->roomType != "Messages" && $this->roomType != "Events" && ($this->roomName === 'published' || $this->roomName === 'archive' || !$this->get('system_settings')->getSettingsValue('room-timer-' . $this->roomName . '-enabled'))) {
                $selectedColumns = array_diff($selectedColumns, array('remainingTime'));
            }

            $columnsList = array();
            foreach ($this->allListColumns as $index => $column) {
                if (isset($this->roomName) && $this->roomType != "Comments" && $this->roomType != "Messages" && $this->roomType != "Events" && ($this->roomName === 'published' || $this->roomName === 'archive' || !$this->get('system_settings')->getSettingsValue('room-timer-' . $this->roomName . '-enabled')) && $index === 'remainingTime') {
                    continue;
                }
                $columnObject = new \stdClass();
                $columnObject->id = $index;
                $columnObject->name = $index;
                if (!in_array($index, $selectedColumns)) {
                    $columnObject->selected = false;
                    $columnsList[] = $columnObject;
                }
            }

            foreach ($selectedColumns as $selectedColumn) {
                $columnObject = new \stdClass();
                $columnObject->id = $selectedColumn;
                $columnObject->name = $selectedColumn;
                $columnObject->selected = true;
                array_push($columnsList, $columnObject);
            }

            return $this->render('IbtikarBackendBundle::changeListColumns.html.twig', array("columnsList" => $columnsList, 'translationDomain' => $this->translationDomain));
        } else if ($request->getMethod() === 'POST') {
            if (is_array($request->get("columns"))) {
                $columsString = implode(",", $request->get("columns"));
            } else {
                $columsString = $request->get("columns");
            }
            if ($staffListColumns) {
                $staffListColumns->setColumns($columsString);
            } else {
                $staffListColumns = new StaffListColumns();
                $staffListColumns->setColumns($columsString);
                $staffListColumns->setStaff($this->getUser());
                if ($this->listName) {
                    $staffListColumns->setListName($this->listName);
                } else {
                    $staffListColumns->setListName(strtolower($this->calledClassName) . "_" . $this->listViewOptions->getListType());
                }
                $dm->persist($staffListColumns);
            }
            $dm->flush();
            return new JsonResponse(array('status' => 'success'));
        }
    }

    protected function configureListColumns() {

    }

    protected function configureTrashColumns() {

    }

    public function getDocumentById($id) {
        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->find($id);
        if (!$document) {
            throw $this->createNotFoundException();
        }
        return $document;
    }


    public function homeAction() {
        return $this->render('IbtikarGlanceDashboardBundle::backendHome.html.twig', array(
                    'translationDomain' => $this->translationDomain
        ));
    }

}
