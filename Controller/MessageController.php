<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GoodyFrontendBundle\Document\ContactMessage;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessageController extends BackendController {

    protected $translationDomain = 'message';
    protected $messageStatus = 'new';
    protected $listName;
    protected $listStatus;
    protected $sublistName = 'New';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "mainTitle" => array(),
            "firstName" => array("isSortable" => false, 'type' => 'refrence', 'getterArguments' => 'createdBy'),
            "lastName" => array("isSortable" => false, 'type' => 'refrence', 'getterArguments' => 'createdBy'),
            "email" => array("isSortable" => false, 'type' => 'refrence', 'getterArguments' => 'createdBy'),
//            "messageType" => array("type" => "translated"),
            "createdAt" => array("type" => "date"),
            "lastAnswerTime" => array("type" => "date"),
            "trackingNumber" => array()
        );
        $this->defaultListColumns = array(
            "firstName",
            "lastName",
            "email",
            "createdAt",
            "lastAnswerTime",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $message) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $queryBuilder = $dm->createQueryBuilder('IbtikarGoodyFrontendBundle:ContactMessage')
                        ->field('messageType')->equals(ContactMessage::$messageTypes['mainThread'])
                        ->field('status')->equals($this->messageStatus);
        $this->listViewOptions->setActions(array('Answer', 'ChangeStatus'));
        $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Message:messageList.html.twig");
    }

    public function listNewMessageAction(Request $message) {
        $this->listStatus = 'list_new_message';
        $this->listName = 'message' . $this->messageStatus . '_' . $this->listStatus;
        return parent::listAction($message);
    }

    public function listInprogressMessageAction(Request $message) {
        $this->listStatus = 'list_inprogress_message';
        $this->listName = 'message' . $this->messageStatus . '_' . $this->listStatus;
        return parent::listAction($message);
    }

    public function listCloseMessageAction(Request $message) {
        $this->listStatus = 'list_close_message';
        $this->listName = 'message' . $this->messageStatus . '_' . $this->listStatus;


        return parent::listAction($message);
    }

    public function changeListNewMessageColumnsAction(Request $message) {
        $this->listStatus = 'list_new_message';
        $this->listName = 'message' . $this->messageStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($message);
    }

    public function changeListCloseMessageColumnsAction(Request $message) {
        $this->listStatus = 'list_close_message';
        $this->listName = 'message' . $this->messageStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($message);
    }

    public function changeListInprogressMessageColumnsAction(Request $message) {
        $this->listStatus = 'list_inprogress_message';
        $this->listName = 'message' . $this->messageStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($message);
    }

    protected function doList(Request $message) {
        $renderingParams = parent::doList($message);
        return $this->getTabCount($renderingParams);
    }

    public function getTabCount($renderingParams = array()) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $renderingParams['newMessageCount'] = $dm->createQueryBuilder('IbtikarGoodyFrontendBundle:ContactMessage')
                        ->field('status')->equals(ContactMessage::$statuses['new'])
                        ->getQuery()->execute()->count();
        $renderingParams['closeMessageCount'] = $dm->createQueryBuilder('IbtikarGoodyFrontendBundle:ContactMessage')
                        ->field('status')->equals(ContactMessage::$statuses['close'])
                        ->getQuery()->execute()->count();
        $renderingParams['inprogressMessageCount'] = $dm->createQueryBuilder('IbtikarGoodyFrontendBundle:ContactMessage')
                        ->field('status')->equals(ContactMessage::$statuses['inprogress'])
                        ->getQuery()->execute()->count();
        return $renderingParams;
    }

    public function getListJsonData($message, $renderingParams) {
        $documentObjects = array();
        foreach ($renderingParams['pagination'] as $document) {
            $templateVars = array_merge(array('object' => $document), $renderingParams);
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
                    if ($this->listViewOptions->hasActionsColumn($this->calledClassName)) {
                        $oneDocument['actions'] = $this->renderView('IbtikarGlanceDashboardBundle:Message:_listActions.html.twig', $templateVars);
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
                elseif ($value == 'lastName' || $value == 'firstName' || $value == 'email') {

                    $oneDocument[$value] = $this->get('app.twig.property_accessor')->propertyAccess($document,'createdBy',$value);
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
        $rowsHeader = $this->getColumnHeaderAndSort($message);
        return new JsonResponse(array('status' => 'success', 'data' => $documentObjects, "draw" => 0, 'sEcho' => 0, 'columns' => $rowsHeader['columnHeader'],
            "recordsTotal" => $renderingParams['total'],
            "recordsFiltered" => $renderingParams['total']));
    }

}
