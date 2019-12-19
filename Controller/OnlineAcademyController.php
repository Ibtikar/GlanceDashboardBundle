<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GoodyFrontendBundle\Document\OnlineAcademy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;

class OnlineAcademyController extends BackendController
{

    protected $translationDomain = 'page';

    public function editAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $onlineAcademy = $dm->getRepository('IbtikarGlanceDashboardBundle:OnlineAcademy')->findOneBy(array('name'=>'onlineacademy'));
        $profilePhoto=$onlineAcademy->getProfilePhoto();


        $form = $this->createFormBuilder($onlineAcademy, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('url', formType\UrlType::class)
                ->add('metaTagTitleAr', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagTitleEn', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagDesciptionAr', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('metaTagDesciptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
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


        return $this->render('IbtikarGlanceDashboardBundle:OnlineAcademy:edit.html.twig', array(
                'form' => $form->createView(),
                'profilePhoto' => $profilePhoto,
                'id'=>$onlineAcademy->getId(),
                'title' => $this->trans('OnlineAcademy page', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }





}
