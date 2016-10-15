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
     * @var \Ibtikar\GlanceDashboardBundle\Service\ListView service object
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
      if( $request->isXmlHttpRequest()){

         return $this->getListJsonData($request);
        }
        $this->listViewOptions = $this->get("list_view");
        $this->listViewOptions->setListType("list");
        $renderingParams = $this->doList($request);
        if ($this->listViewOptions->getTemplate()) {
            return $this->render($this->listViewOptions->getTemplate(), $renderingParams);
        } else {
            return $this->render('IbtikarGlanceDashboardBundle:List:baseList.html.twig', $renderingParams);
        }
    }

    public function trashAction(Request $request) {
        $this->listViewOptions = $this->get("list_view");
        $this->listViewOptions->setListType("trash");
        $renderingParams = $this->doList($request);
        return $this->render('IbtikarGlanceDashboardBundle:List:baseList.html.twig', $renderingParams);
    }

    protected function getCurrentColumns($listName) {
        /* Get List Fields */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository('IbtikarGlanceDashboardBundle:StaffListColumns')->findOneBy(array('staff.$id' => new \MongoId($this->getUser()->getId()), "listName" => $listName));
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
            $listName = 'ibtikar_glance_dashboard_'.strtolower($this->calledClassName) . '_' . $this->listViewOptions->getListType();
        }

        $selectedColumns = $this->getCurrentColumns($listName);


        foreach ($selectedColumns as $column) {
            // check if the column was removed from the main list columns
            if (isset($this->allListColumns[$column])) {
                $fieldOptions = $this->allListColumns[$column];
                // change with option of datatable
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
                $this->listViewOptions->addField($column, $column, $fieldOptions["type"], $fieldOptions["sortOrderType"], $fieldOptions["tooltip"], $fieldOptions["getterArguments"], $fieldOptions["isSortable"], $fieldOptions["isClickable"], $fieldOptions["class"], $fieldOptions["document"]);
            }
        }



        $sortBy = $this->listViewOptions->getDefaultSortBy();
        $sortOrder = $this->listViewOptions->getDefaultSortOrder();

        $queryBuilder = $this->listViewOptions->getListQueryBuilder();

        if (is_null($request->get('sort')) || !in_array($request->get('sort'), $selectedColumns)) {
            $queryBuilder = $queryBuilder->sort($sortBy, $sortOrder);
        }

        $limit = $request->get('limit');
        if (!$limit || !in_array($limit, array(2,10, 20, 50))) {
            $limit =2;// $this->container->getParameter('per_page_items');
        }

        $pageNumber = $request->query->get('page', 1);
        if ($pageNumber < 1) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $pagination = $queryBuilder->skip(($pageNumber-1)*$limit)->limit($limit)->getQuery()->execute();

        if ($this->listName) {
            $changeListColumnType = '_' . $this->listStatus;
        } else {
            $changeListColumnType = '';
        }
        $sublistName = $this->calledClassName;
        if ($this->sublistName) {
            $sublistName = $this->sublistName;
        }
//        var_dump($this->listViewOptions->getFields());
//        exit;

        $prepareColumns=array(array('data'=>'id','orderable'=>false));
        foreach($this->listViewOptions->getFields() as $name=>$value ){
            $column=array('data'=>$name,'orderable'=>$value->isSortable);
            $prepareColumns[]=$column;

            }
        $renderningParams = array(
            'sublistName' => $sublistName,
            'total' => $pagination->count(),
            'limit' => $limit,
            'skip'=> ($pageNumber-1)*$limit,
            'pagination' => $pagination,
            'translationDomain' => $this->translationDomain,
            'listName' => $this->calledClassName,
            'list' => $this->listViewOptions,
            'columns'=> json_encode(array_values($prepareColumns)),
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
    }

    protected function createQueryBuilder($documentBundle = "IbtikarGlanceDashboardBundle") {
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

    public function trans($string, $param = array(), $translationDomain = 'messages') {
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

        if ($request->get("listType","list") == "list") {
            $this->listViewOptions->setListType("list");
            $this->configureListColumns();
        }
        if ($request->get("listType") == "trash") {
            $this->listViewOptions->setListType("trash");
            $this->configureTrashColumns();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($this->listName) {
            $staffListColumns = $dm->getRepository('IbtikarGlanceDashboardBundle:StaffListColumns')->findOneBy(array('staff.$id' => new \MongoId($this->getUser()->getId()), "listName" => $this->listName));
        } else {
            $staffListColumns = $dm->getRepository('IbtikarGlanceDashboardBundle:StaffListColumns')->findOneBy(array('staff.$id' => new \MongoId($this->getUser()->getId()), "listName" =>'ibtikar_glance_dashboard_'. strtolower($this->calledClassName) . "_" . $this->listViewOptions->getListType()));
        }
        if ($request->getMethod() === 'GET') {
            if ($staffListColumns && $staffListColumns->getColumns()) {
                $selectedColumns = explode(",", $staffListColumns->getColumns());
            } else {
                $selectedColumns = $this->defaultListColumns;
            }

            $columnsList = array();

            foreach ($this->allListColumns as $index => $column) {
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

            return $this->render('IbtikarGlanceDashboardBundle::changeListColumns.html.twig', array("columnsList" => $columnsList, 'translationDomain' => $this->translationDomain));
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
                    $staffListColumns->setListName('ibtikar_glance_dashboard_'.$this->listName);
                } else {
                    $staffListColumns->setListName(strtolower($this->calledClassName) . "_" . $this->listViewOptions->getListType());
                }
                $dm->persist($staffListColumns);
            }
            $selectedColumns = explode(",", $staffListColumns->getColumns());
        foreach ($selectedColumns as $column) {
            // check if the column was removed from the main list columns
            if (isset($this->allListColumns[$column])) {
                $fieldOptions = $this->allListColumns[$column];
                    // change with option of datatable
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
                    $this->listViewOptions->addField($column, $column, $fieldOptions["type"], $fieldOptions["sortOrderType"], $fieldOptions["tooltip"], $fieldOptions["getterArguments"], $fieldOptions["isSortable"], $fieldOptions["isClickable"], $fieldOptions["class"], $fieldOptions["document"]);
                }
            }



            $dm->flush();
            $prepareColumns = array(array('data'=>'id','orderable'=>false));
            foreach ($this->listViewOptions->getFields() as $name => $value) {
                $column = array('data' => $name, 'orderable' => $value->isSortable);
                $prepareColumns[] = $column;
            }
            return new JsonResponse(array('status' => 'success','column'=>$prepareColumns));
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
        ));
    }

    public function backendNotFoundAction() {
        $response = new Response();
        $response->setStatusCode(404);
        return $this->render('IbtikarGlanceDashboardBundle:Exception:error.html.twig', array('exception' => new \Exception('Wrong id'), 'status_code' => 404), $response);
    }

    public function checkFieldUniqueAction(Request $request) {
        $securityContext = $this->container->get('security.authorization_checker');

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $fieledName = $request->get('fieldName');
        $fieledValue = $request->get('fieldValue');
        $id = $request->get('id');
        $em = $this->get('doctrine_mongodb')->getManager();
        if ($fieledName == 'email') {
            $fieledValue = strtolower($fieledValue);
        }
        $count = $em->createQueryBuilder($this->getObjectShortName())
                        ->field('deleted')->equals(FALSE)
                        ->field($fieledName)->equals(trim($fieledValue));
        if ($id) {
            $count = $count->field('id')->notEqual($id);
        }
        $count = $count->getQuery()->execute()->count();
        if ($count > 0) {
            return new JsonResponse(array('status' => 'success', 'unique' => FALSE, 'message' => $this->trans('not valid')));
        } else {
            return new JsonResponse(array('status' => 'success', 'unique' => TRUE, 'message' => $this->trans('valid')));
        }
    }

    public function getListJsonData($request) {

    }

}
