<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Message;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessageController extends BackendController {

    protected $translationDomain = 'message';
    protected $messageStatus = 'new';
    protected $listName;
    protected $listStatus;
    protected $sublistName = 'New';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array(),
            "type" => array("type" => "translated"),
            "createdBy" => array("isSortable" => false),
            "createdAt" => array("type" => "date"),
            "updatedAt" => array("type" => "date"),
        );
        $this->defaultListColumns = array(
            "title",
            "createdAt",
            "createdBy",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $message) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Message')
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

        $renderingParams['newMessageCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Message')
                        ->field('status')->equals(Message::$statuses['new'])
                        ->getQuery()->execute()->count();
        $renderingParams['closeMessageCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Message')
                        ->field('status')->equals(Message::$statuses['close'])
                        ->getQuery()->execute()->count();
        $renderingParams['inprogressMessageCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Message')
                        ->field('status')->equals(Message::$statuses['inprogress'])
                        ->getQuery()->execute()->count();
        return $renderingParams;
    }

    public function getListJsonData($message, $renderingParams) {
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
                                $actionTd.= '<a class="btn btn-default dev-delete-single-message"  data-href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_delete', array('id' => $document->getId())) . '" data-id="' . $document->getId() . '" ><i class="icon-trash" data-popup="tooltip" title="' . $this->trans('Delete ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '" data-placement="right"></i></a>';
                            } elseif ($action == 'ViewOne' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_VIEWONE'))) {
                                $actionTd.= '<a class="btn btn-default"  href = "' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_view', array('id' => $document->getId())) . '" ><i class="icon-eye" data-popup="tooltip"  title="' . $this->trans('View One ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"  data-placement="right" ></i></a>';
                            } elseif ($action == 'Assign' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ASSIGN'))) {
                                $actionTd.= '<a class="btn btn-default dev-assign-to-me" href="javascript:void(0);"  data-url="' . $this->generateUrl($this->listViewOptions->getBundlePrefix() . strtolower($this->calledClassName) . '_assign_to_me') . '" data-id="' . $document->getId() . '"><i class="icon-user"  title="' . $this->trans('AssignToMe', array(), $this->translationDomain) . '"  data-popup="tooltip" data-placement="right"></i></a>';
                            } elseif ($action == 'Publish' && ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH'))) {
                                $actionTd.= '<a href="javascript:void(0)" data-toggle="modal"  class="btn btn-default dev-publish-message" data-id="' . $document->getId() . '"><i class="icon-share" data-placement="right"  data-popup="tooltip" title="' . $this->trans('publish ' . ucfirst($this->calledClassName), array(), $this->translationDomain) . '"></i></a>
';
                            }
                        }

                        $oneDocument['actions'] = $actionTd;
                        continue;
                    }
                }
                $getfunction = "get" . ucfirst($value);
                if ($value == 'name' && $document instanceof \Ibtikar\GlanceUMSBundle\Document\Role) {
                    $oneDocument[$value] = '<a class="dev-role-getPermision" href="javascript:void(0)" data-id="' . $document->getId() . '">' . $document->$getfunction() . '</a>';
                } elseif ($value == 'username') {
                    $image = $document->getWebPath();
                    if (!$image) {
                        $image = 'bundles/ibtikarshareeconomydashboarddesign/images/profile.jpg';
                    }
                    $oneDocument[$value] = '<div class="media-left media-middle"><a href="javascript:void(0)">'
                            . '<img src="/' . $image . '" class="img-circle img-lg" alt=""></a></div>
                                                <div class="media-body">
                                                    <a href="javascript:void(0);" class="display-inline-block text-default text-semibold letter-icon-title">  ' . $document->$getfunction() . ' </a>
                                                </div>';
                } elseif ($value == 'profilePhoto') {
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
