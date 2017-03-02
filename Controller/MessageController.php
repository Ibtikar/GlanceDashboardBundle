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
        $this->listViewOptions->setActions(array('ChangeStatus','ViewOne'));
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


    public function changeStatusAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $securityContext = $this->get('security.authorization_checker');
        $publishOperations = $this->get('recipe_operations');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_CHANGESTATUS') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'failed-reload', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse(array_merge($result, $this->getTabCount()));
        }

        if ($request->getMethod() === 'GET') {
            $id = $request->get('id');
            if (!$id) {
                return $this->getFailedResponse();
            }

            $message = $dm->getRepository('IbtikarGoodyFrontendBundle:ContactMessage')->findOneById($id);
            if (!$message)
                throw $this->createNotFoundException($this->trans('Wrong id'));

            if ($message->getStatus() != $this->messageStatus) {
                $result = array('status' => 'failed-reload', 'message' => $this->trans('not done'));
                return new JsonResponse(array_merge($result, $this->getTabCount()));
            }

            return $this->render('IbtikarGlanceDashboardBundle:Message:changeStatusModal.html.twig', array(
                    'translationDomain' => $this->translationDomain,
                    'statuses' => ContactMessage::$statuses,
                    'prefixRoute' => 'ibtikar_glance_dashboard_' . $this->calledClassName,
                    'document' => $message
            ));
        } else if ($request->getMethod() === 'POST') {

            $message = $dm->getRepository('IbtikarGoodyFrontendBundle:ContactMessage')->findOneById($request->get('documentId'));
            if (!$message) {
                $result = array('status' => 'failed-reload', 'message' => $this->trans('not done'));
                return new JsonResponse(array_merge($result, $this->getTabCount()));
            }

            $messageStatus = $message->getStatus();
            $status = $request->get('status');
            if ($status == $messageStatus) {
                $result = array('status' => 'failed-reload', 'message' => $this->trans('not done'));
                return new JsonResponse(array_merge($result, $this->getTabCount()));
            }


            $message->setStatus($status);
            $dm->flush();
            $data = array('status' => 'success','message' => $this->trans('done sucessfully'));
            return new JsonResponse(array_merge($data, $this->getTabCount()));
        }
    }

    public function viewAction(Request $request , $id){
        $dm = $this->get('doctrine_mongodb')->getManager();
        $user = $this->getUser();
        if (is_null($user)) {
            throw $this->createNotFoundException('Access Denied');
        }
        $document = $dm->getRepository('IbtikarGoodyFrontendBundle:ContactMessage')->findOneBy(array('id' => $id));
        if (!$document || $document->getMessageType() !== ContactMessage::$messageTypes['mainThread']) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $locale = $request->get('_locale');
        $mediaData = array();
        $repliesId=array();
        $replies = $document->getReplies();
        if ($replies) {
            foreach ($replies as $reply) {
                $repliesId[] = $reply->getId();
            }
        }
        $contactMessagesMedia = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->getContactMessagesMedia(array_merge($repliesId,array($id)));
        foreach ($contactMessagesMedia as $contactMessageMedia) {
            $mediaData[$contactMessageMedia->getContactMessage()->getId()]['images'][] = $contactMessageMedia;
        }
        $responseBuilder = $this->container->get('response_builder');

        $data['document'] = array(
            'id' => $document->getId(),
            'title' => $document->getMainTitle(),
            'date' => $document->getCreatedAt()->format('d') ." ". $this->trans($document->getCreatedAt()->format('F'), array(), 'app') . ',' . $document->getCreatedAt()->format('Y'),
            'time' => $document->getCreatedAt()->format('h:i A'),
            'content' => $document->getContent(),
            'type' => $this->trans($document->getContactType(), array(), 'contact'),
            'status' => $this->trans($document->getStatus(), array(), 'contact'),
            'trackingNumber' =>$document->getTrackingNumber() ,
            'createdBy' =>$document->getCreatedBy() ,
            'createdAt' =>$document->getCreatedAt() ,
        );
        $repliesId=array();
        $replies = $document->getReplies();
        if ($replies) {
            foreach ($replies as $reply) {
                $repliesId[] = $reply->getId();
            }
            $replies = $dm->createQueryBuilder('IbtikarGoodyFrontendBundle:ContactMessage')
                            ->field('id')->in($repliesId)
                            ->sort('createdAt', 'ASC')
                            ->eagerCursor(true)
                            ->getQuery()->execute();

        }

        return $this->render('IbtikarGlanceDashboardBundle:Message:view.html.twig', array(
                    'translationDomain' => $this->translationDomain,
                    'replies' => $replies,
                    'document' => $data['document'],
                    'room'=>$this->calledClassName,
                    'mediaData' => $mediaData,
        ));
    }


    public function replyAction(Request $request, $id)
    {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_ANSWER') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $request->getSession()->getFlashBag()->add('error', $this->get('translator')->trans('You are not authorized to do this action any more'));
            return new JsonResponse(array('status' => 'success', 'message' => $this->get('translator')->trans('You are not authorized to do this action any more')));
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $originalMessage = $dm->getRepository('IbtikarGoodyFrontendBundle:ContactMessage')->find($id);

        if (!$originalMessage ){
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));
            return $this->getFailedResponse('failed-reload');
        }
        $message=$request->get('message');
        if(!$message){
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));
            return $this->getFailedResponse('failed-reload');
        }

        $contactMessage = new ContactMessage();
        $contactMessage->setMainTitle($originalMessage->getMainTitle());
        $contactMessage->setMessageType(ContactMessage::$messageTypes['staffReply']);
        $contactMessage->setContactType($originalMessage->getContactType());
        $contactMessage->setStatus(ContactMessage::$statuses['inprogress']);
        $contactMessage->setContent($message);
        $dm->persist($contactMessage);

        $originalMessage->addReply($contactMessage);
        $originalMessageStatus=$originalMessage->getStatus();
        $originalMessage->setStatus(ContactMessage::$statuses['inprogress']);
        $dm->flush();
        $emailTemplate = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:EmailTemplate')->findOneBy(array('name' => 'reply on your request'));
        $body = str_replace(
            array(
            '%user-name%',
            '%answer%',
            '%trackingNumber%',
            ), array(
            $originalMessage->getCreatedBy()->__toString(),
            $contactMessage->getContent(),
            $originalMessage->getTrackingNumber(),
            ), str_replace('%message%', $emailTemplate->getTemplate(), $this->get('frontend_base_email')->getBaseRender2($originalMessage->getCreatedBy()->getPersonTitle(), false))
        );
        $mailer = $this->get('swiftmailer.mailer.spool_mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($emailTemplate->getSubject())
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($originalMessage->getCreatedBy()->getEmail())
            ->setBody($body, 'text/html')
        ;
        $mailer->send($message);
        $request->getSession()->getFlashBag()->add('success',  in_array($originalMessageStatus, array(ContactMessage::$statuses['new'],ContactMessage::$statuses['close'])) ?$this->get('translator')->trans('change status',array(),  $this->translationDomain):$this->get('translator')->trans('done sucessfully'));
        return new JsonResponse(array('status' => 'success', 'message' => 'done sucessfully'));
    }
}
