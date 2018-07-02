<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use \Ibtikar\GlanceDashboardBundle\Document\HomeBanner;

class PageController extends BackendController
{

    protected $translationDomain = 'competition';

    public function editAboutUsAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $aboutUs = $dm->getRepository('IbtikarGlanceDashboardBundle:Page')->findOneBy(array('name'=>'about us'));


        $form = $this->createFormBuilder($aboutUs, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('title', formType\TextType::class, array('required' => TRUE))
                ->add('titleEn', formType\TextType::class, array('required' => TRUE))
                ->add('brief', formType\TextareaType::class, array('required' => TRUE))
                ->add('briefEn', formType\TextareaType::class, array('required' => TRUE))
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


        return $this->render('IbtikarGlanceDashboardBundle:Page:aboutus.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('about us', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }





}
