<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Ibtikar\GlanceDashboardBundle\Document\Stars;
use Ibtikar\GlanceDashboardBundle\Document\Export;

class StarsController extends BackendController {

    protected $translationDomain = 'stars';

    protected $starsStatus = 'new';
    protected $listName;
    protected $listStatus;
    protected $sublistName = 'New';


    protected function configureListColumns()
    {
        $this->allListColumns = array(
            "name" => array("searchFieldType" => "input"),
            "mobile" => array("isSortable" => false,'class'=>'phoneNumberLtr'),
            "email" => array("isSortable" => false, 'type' => 'refrence', 'getterArguments' => 'createdBy'),
            "createdAt" => array("type" => "date"),
        );
        $this->defaultListColumns = array(
            "name",
            "mobile",
            "email",
            "createdAt",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $request)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $this->listViewOptions->setBulkActions(array("Export"));
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Stars:list.html.twig");
    }

    public function listNewStarsAction(Request $request)
    {
        $this->listStatus = 'list_new_stars';
        $this->listName = 'stars' . $this->starsStatus . '_' . $this->listStatus;


        return parent::listAction($request);
    }

    public function listRejectedStarsAction(Request $request)
    {
        $this->listStatus = 'list_rejected_stars';
        $this->listName = 'stars' . $this->starsStatus . '_' . $this->listStatus;


        return parent::listAction($request);
    }

    public function listAcceptedStarsAction(Request $request)
    {
        $this->listStatus = 'list_approved_stars';
        $this->listName = 'stars' . $this->starsStatus . '_' . $this->listStatus;


        return parent::listAction($request);
    }

    public function changeListNewStarsColumnsAction(Request $request)
    {
        $this->listStatus = 'list_new_stars';
        $this->listName = 'stars' . $this->starsStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }
    public function changeListRejectedStarsColumnsAction(Request $request)
    {
        $this->listStatus = 'list_rejected_stars';
        $this->listName = 'stars' . $this->starsStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }
    public function changeListApprovedStarsColumnsAction(Request $request)
    {
        $this->listStatus = 'list_approved_stars';
        $this->listName = 'stars' . $this->starsStatus . '_' . $this->listStatus;
        return parent::changeListColumnsAction($request);
    }

    protected function doList(Request $request)
    {
        $renderingParams = parent::doList($request);
        return $this->getTabCount($renderingParams);
    }


    public function editAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $settings = $this->get('systemSettings')->getSettingsRecordsByCategory('stars');
        $settingsArray = $this->convertRecordsToArray($settings);


        if(count($settingsArray) != 8){
            throw new \Exception("Settings fixtures are not loaded.");
        }

        $form = $this->createFormBuilder(null, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('briefAr', formType\TextareaType::class, array('data' => $settingsArray["stars-brief-ar"], 'required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('briefEn', formType\TextareaType::class, array('data' => $settingsArray["stars-brief-en"], 'required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('benefitsAr', CKEditorType::class, array('data' => $settingsArray["stars-benefits-ar"], 'required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-ckmin' => 10, 'data-rule-ckmax' => 1000, 'data-rule-ckreq' => true, 'data-error-after-selector' => '.dev-after-element')))
                ->add('benefitsEn', CKEditorType::class, array('data' => $settingsArray["stars-benefits-en"], 'required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-ckmin' => 10, 'data-rule-ckmax' => 1000, 'data-rule-ckreq' => true, 'data-error-after-selector' => '.dev-after-element')))
                ->add('metaTagTitleAr', formType\TextType::class, array('data' => $settingsArray["metaTagTitleAr"],'required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagTitleEn', formType\TextType::class, array('data' => $settingsArray["metaTagTitleEn"],'required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagDesciptionAr', formType\TextareaType::class, array('data' => $settingsArray["metaTagDesciptionAr"],'required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('metaTagDesciptionEn', formType\TextareaType::class, array('data' => $settingsArray["metaTagDesciptionEn"],'required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->getForm();

        $image = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findOneBy(array(
            'collectionType' => 'Stars'
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            if ($form->isValid()){
                foreach ($settings as $record) {
                    switch ($record->getKey()) {
                        case "stars-brief-ar":
                            $record->setValue($data['briefAr']);
                            $record->setMetaTagTitleAr($data['metaTagTitleAr']);
                            $record->setMetaTagDesciptionAr($data['metaTagDesciptionAr']);
                            $record->setMetaTagTitleEn($data['metaTagTitleEn']);
                            $record->setMetaTagDesciptionEn($data['metaTagDesciptionEn']);
                            break;
                        case "stars-brief-en":
                            $record->setValue($data['briefEn']);
                            break;
                        case "stars-benefits-ar":
                            $record->setValue($data['benefitsAr']);
                            break;
                        case "stars-benefits-en":
                            $record->setValue($data['benefitsEn']);
                    }
                }

                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Stars:edit.html.twig', array(
            'form' => $form->createView(),
            'translationDomain' => $this->translationDomain,
            'title' => $this->trans('Edit Stars', array(), $this->translationDomain),
            'coverImage' => $image
        ));
    }

    private function convertRecordsToArray($settingsRecords) {
        $settings = array();
        foreach ($settingsRecords as $record) {
            if($record->getKey()=='stars-brief-ar'){
                $settings['metaTagTitleAr']=$record->getMetaTagTitleAr();
                $settings['metaTagDesciptionAr']=$record->getMetaTagDesciptionAr();
                $settings['metaTagTitleEn']=$record->getMetaTagTitleEn();
                $settings['metaTagDesciptionEn']=$record->getMetaTagDesciptionEn();
            }
            $settings[$record->getKey()] = $record->getValue();
        }
        return $settings;
    }

     public function getTabCount($renderingParams = array())
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $renderingParams['newStarsCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Stars')
                ->field('status')->equals(Stars::$statuses['new'])
                ->field('assignedTo')->exists(FALSE)
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $renderingParams['approvedStarsCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Stars')
                ->field('status')->equals(Stars::$statuses['approved'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        $renderingParams['rejectedStarsCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Stars')
                ->field('status')->equals(Stars::$statuses['rejected'])
                ->field('deleted')->equals(false)
                ->getQuery()->execute()->count();
        return $renderingParams;
    }

    public function approveAction(Request $request) {
        return $this->statusChange($request->get('id'),'approved','ROLE_STARS'.  strtoupper($this->starsStatus).'_APPROVE');
    }

    public function rejectAction(Request $request) {
        return $this->statusChange($request->get('id'),'rejected','ROLE_STARS'.  strtoupper($this->starsStatus).'_REJECT');
    }

    private function statusChange($id,$status,$permission) {

        if (!in_array($status, Stars::$statuses)) {
            throw new \Exception('Wrong Stars Status');
        }

        $securityContext = $this->get('security.authorization_checker');

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        if (!$securityContext->isGranted($permission) && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }
        if (!$id) {
            return $this->getFailedResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();

        $document = $dm->getRepository('IbtikarGlanceDashboardBundle:Stars')->find($id);
        if (!$document || $document->getDeleted()) {
            return new JsonResponse($this->getTabCount(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation'))));
        }

        $document->setStatus(Stars::$statuses[$status]);
        $document->getUser()->setStar($status == 'approved'?true:false);

        $dm->flush();

        $this->statusChangeEmail($status,$document->getUser());

        return new JsonResponse($this->getTabCount(array('status' => 'success', 'message' => $this->get('translator')->trans('done sucessfully'))));
    }

    private function statusChangeEmail($status ,$user=NULL) {
        if (!$user) {
            $user = $this->getUser();
        }
        $emailTemplate = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:EmailTemplate')->findOneBy(array('name' => $status == "approved"?'join stars':'reject stars'));
                $body = str_replace(
                        array(
                    '%user-name%',
                        ), array(
                    $user->__toString(),
                    ), str_replace('%message%', $emailTemplate->getTemplate(), $this->get('frontend_base_email')->getBaseRender2($user->getPersonTitle(), false))
                );
                $mailer = $this->get('swiftmailer.mailer.spool_mailer');
                $message = \Swift_Message::newInstance()
                        ->setSubject($emailTemplate->getSubject())
                        ->setFrom($this->container->getParameter('mailer_user'))
                        ->setTo($user->getEmail())
                        ->setBody($body, 'text/html')
                ;
                $mailer->send($message);
    }

        public function viewAction(Request $request, $id)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $star = $dm->getRepository('IbtikarGlanceDashboardBundle:Stars')->find($id);
        if (!$star) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        return $this->render('IbtikarGlanceDashboardBundle:Stars:view.html.twig', array(
                'translationDomain' => 'stars',
                'star' => $star,

        ));
    }

      public function exportAction(Request $request)
    {
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $this->listViewOptions = $this->get("list_view");
        $this->listViewOptions->setListType("list");
        $renderingParams = $this->doList($request);
        $securityContext = $this->container->get('security.authorization_checker');



        if (!$securityContext->isGranted('ROLE_ADMIN') && !$securityContext->isGranted('ROLE_'. strtoupper($this->calledClassName).'_EXPORT')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }

        $ids = $request->get('ids', array());

        $params = $request->query->all();
        if (!empty($ids)) {
            $params['ids'] = $ids;
        }
        $params['status']=$this->starsStatus;
//
//        $ext = $params['ext'];
//        unset($params['ext']);

        $export = new Export();
        $export->setName(uniqid("star-"));
        $export->setParams($params);
        $export->setExtension('xls');
        $export->setFields($this->getCurrentColumns('ibtikar_glance_dashboard_'. strtolower($this->calledClassName).'export'));
        $export->setState(Export::READY);
        $export->setType(Export::STARS);

        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($export);
        $dm->flush();

        return new JsonResponse(array('status' => 'success', 'message' => $this->get('translator')->trans('file export will take sometime', array(), $this->translationDomain)));
    }

}
