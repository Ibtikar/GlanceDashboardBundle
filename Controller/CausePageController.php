<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\CausePage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;

class CausePageController extends BackendController
{

    protected $translationDomain = 'page';

    public function editAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $causePage = $dm->getRepository('IbtikarGlanceDashboardBundle:CausePage')->findOneBy(array('name'=>'cause page'));
        $profilePhoto=$causePage->getProfilePhoto();


        $form = $this->createFormBuilder($causePage, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('brief',  CKEditorType::class, array('required' => TRUE,'attr' => array('data-validate-element'=>true,'data-rule-ckmin' => 10,'data-rule-ckmax' => 1000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
                ->add('briefEn',  CKEditorType::class, array('required' => TRUE,'attr' => array('data-validate-element'=>true,'data-rule-ckmin' => 10,'data-rule-ckmax' => 1000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
                ->add('terms',  CKEditorType::class, array('required' => TRUE,'attr' => array('data-validate-element'=>true,'data-rule-ckmin' => 10,'data-rule-ckmax' => 1000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
                ->add('termsEn',  CKEditorType::class, array('required' => TRUE,'attr' => array('data-validate-element'=>true,'data-rule-ckmin' => 10,'data-rule-ckmax' => 1000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
                ->add('competition', \Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType::class, array('required' => false,'placeholder' => 'Choose Competition','class' => 'IbtikarGlanceDashboardBundle:Competition', 'attr' => array('data-img-method'=>'profilePhoto','data-img-default'=>'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg','class' => 'select')))
                ->add('save', formType\SubmitType::class)
                ->getForm();


        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

            }
        }


        return $this->render('IbtikarGlanceDashboardBundle:CausePage:edit.html.twig', array(
                'form' => $form->createView(),
                'profilePhoto' => $profilePhoto,
                'id'=>$causePage->getId(),
                'title' => $this->trans('cause page', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }





}
