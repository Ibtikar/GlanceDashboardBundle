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
        $this->listViewOptions = $this->get("list_view");
        $this->listViewOptions->setListType("list");
        $renderingParams = $this->doList($request);
        if( $request->isXmlHttpRequest()){

         return $this->getListJsonData($renderingParams);
        }

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


        if (is_null($request->get('columnDir')) || !in_array($request->get('columnDir'), array('desc', 'asc'))) {
            $sortOrder = $this->listViewOptions->getDefaultSortOrder();
        }else{
          $sortOrder=  $request->get('columnDir');
        }
        if (is_null($request->get('sort')) || !in_array($request->get('sort'), $selectedColumns)) {
            $sortBy = $this->listViewOptions->getDefaultSortBy();
        }else{
           $sortBy =$request->get('sort');
        }

        $queryBuilder = $this->listViewOptions->getListQueryBuilder();

        $queryBuilder = $queryBuilder->sort($sortBy, $sortOrder);

        $limit = $request->get('limit');
        if (!$limit || !in_array($limit, array(10, 20, 50))) {
            $limit = $this->container->getParameter('per_page_items');
        }

        $pageNumber = $request->query->get('page', 1);
        if ($pageNumber < 1) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $pagination = $queryBuilder->skip(($pageNumber-1)*$limit)->limit($limit)->getQuery()->execute();

        $sublistName = $this->calledClassName;
        if ($this->sublistName) {
            $sublistName = $this->sublistName;
        }
        $index = 0;
        $prepareColumns = array();
        $sortIndex=null;
        $columnArray=array();
        if (count($this->listViewOptions->getBulkActions()) > 0) {
            $prepareColumns = array(array('data' => 'id', 'orderable' => false));
            $columnArray[]='id';
            $index++;
        }
        foreach ($this->listViewOptions->getFields() as $name => $value) {
            $column = array('data' => $name, 'orderable' => $value->isSortable,'data-name'=>$name,'title'=>  $this->trans($name,array(),  $this->translationDomain));
            $prepareColumns[] = $column;
            $columnArray[]=$name;
            if ($sortBy == $name) {
                $sortIndex = $index;
            }
            $index++;
        }
        if(count($this->listViewOptions->getActions()) > 0){
            $prepareColumns[]=array('data' => 'actions', 'orderable' => FALSE);
            $columnArray[]='actions';
        }

        $renderningParams = array(
            'sublistName' => $sublistName,
            'total' => $pagination->count(),
            'limit' => $limit,
            'skip'=> ($pageNumber-1)*$limit,
            'pagination' => $pagination,
            'translationDomain' => $this->translationDomain,
            'listName' => $this->calledClassName,
            'prefixRoute' => 'ibtikar_glance_dashboard_'.$this->calledClassName,
            'list' => $this->listViewOptions,
            'columns'=> json_encode(array_values($prepareColumns)),
            'columnArray'=> $columnArray ,
        );
        if ($sortIndex === 0 || $sortIndex) {
            $renderningParams['sort'] = json_encode(array($sortIndex, $sortOrder));
        }

        return $renderningParams;
    }

    protected function configureListParameters(Request $request) {

    }

    protected function configureTrashParameters(Request $request) {

    }

    private function prepareListParameters() {
        $this->listViewOptions->setListQueryBuilder($this->createQueryBuilder());
        $this->listViewOptions->setDefaultSortBy("updatedAt");
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
            $staffListColumns = $dm->getRepository('IbtikarGlanceDashboardBundle:StaffListColumns')->findOneBy(array('staff.$id' => new \MongoId($this->getUser()->getId()), "listName" => 'ibtikar_glance_dashboard_'.$this->listName));
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
                    $staffListColumns->setListName('ibtikar_glance_dashboard_'.strtolower($this->calledClassName) . "_" . $this->listViewOptions->getListType());
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
            $columnHeader=$this->getColumnHeaderAndSort($request);

            return new JsonResponse(array('status' => 'success','column'=>$columnHeader['columnHeader'],'sort'=>$columnHeader['sort']));
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

    public function getListJsonData($renderingParams)
    {
        $documentObjects = array();
        foreach ($renderingParams['pagination'] as $document) {
            $oneDocument = array();

            foreach ($renderingParams['columnArray'] as $value) {
                if ($value == 'id') {
                    $oneDocument['id'] = '<div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" class="styled" data-id=' . $document->getId() . '>
                                    </label>
                              </div>';
                    continue;
                }
                if ($value == 'actions') {
                    $security = $this->container->get('security.authorization_checker');
                    $actionTd = '';
                    if (count($this->listViewOptions->getActions()) > 0) {
                        foreach ($this->listViewOptions->getActions() as $action) {
                            if ($action == 'Edit' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . $this->listName . '_EDIT'))) {
                                $actionTd.= '<a class="btn btn-defualt"  href = "' . $this->generateUrl('ibtikar_glance_dashboard_' . strtolower($this->calledClassName) . '_edit', array('id' => $document->getId())) . '" title="' . $this->trans('Edit Role', array(), $this->translationDomain) . '" data-popup="tooltip"  data-placement="bottom" ><i class="icon-pencil"></i></a>';
                            }
                        }

                        $oneDocument['actions'] = $actionTd;
                        continue;
                    }
                }
                $getfunction = "get" . ucfirst($value);
                if ($value == 'name' && $document instanceof \Ibtikar\GlanceDashboardBundle\Document\Role) {
                    $oneDocument[$value] = '<a class="dev-role-getPermision" href="javascript:void(0)" data-id="' . $document->getId() . '">' . $document->$getfunction() . '</a>';
                } elseif ($document->$getfunction() instanceof \DateTime) {
                    $oneDocument[$value] = $document->$getfunction() ? $document->$getfunction()->format('Y-m-d') : null;
                } else {
                    $fieldData=$document->$getfunction();
                    $oneDocument[$value] = is_object($fieldData)?$fieldData->__toString():$fieldData;
                }
            }

            $documentObjects[] = $oneDocument;
        }
        return new JsonResponse(array('data' => $documentObjects, "draw" => 0, 'sEcho' => 0,
            "recordsTotal" => $renderingParams['total'],
            "recordsFiltered" => $renderingParams['total']));
    }

    public function getColumnHeaderAndSort($request)
    {
        $this->configureListParameters($request);
        $sortIndex = null;
        $index = 0;
        $prepareColumns = array();
        if ($this->listViewOptions->getBulkActions()) {
            $prepareColumns = array(array('data' => 'id', 'orderable' => false, 'title' => ''));
            $index++;
        }
        foreach ($this->listViewOptions->getFields() as $name => $value) {
            $column = array('data' => $name, 'orderable' => $value->isSortable, 'title' => $this->trans($name, array(), $this->translationDomain), 'name' => $name);
            $prepareColumns[] = $column;
            if ($this->listViewOptions->getDefaultSortBy() == $name) {
                $sortIndex = $index;
            }
            $index++;
        }
        if (count($this->listViewOptions->getActions()) > 0) {
            $prepareColumns[] = array('data' => 'actions', 'orderable' => FALSE, 'name' => 'actions', 'title' => $this->trans('actions', array(), $this->translationDomain));
        }
        if ($sortIndex) {
            $sort = json_encode(array($sortIndex, $this->listViewOptions->getDefaultSortOrder()));
        } else {
            $sort = null;
        }
        return array('columnHeader' => $prepareColumns, 'sort' => $sort);
    }
}
