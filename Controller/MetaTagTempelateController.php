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

class MetaTagTempelateController extends BackendController {

    protected $translationDomain = 'bannar';

    public function editAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $tempelate = $dm->getRepository('IbtikarGlanceDashboardBundle:MetaTagTempelate')->findOneBy(array('shortName' => 'recipe'));
//        if (!$tempelate) {
//            $tempelate = new \Ibtikar\GlanceDashboardBundle\Document\MetaTagTempelate();
//        }
        $form = $this->createFormBuilder($tempelate, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('recipeMetaTagTitleAr', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('recipeMetaTagDescriptionAr', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('articleMetaTagTitleAr', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('articleMetaTagdecriptionAr', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('recipeMetaTagTitleEn', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('recipeMetaTagDescriptionEn', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('articleMetaTagTitleEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('articleMetaTagdecriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('save', formType\SubmitType::class)
                ->getForm();


        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->persist($tempelate);
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }


        return $this->render('IbtikarGlanceDashboardBundle:MetaTagTempelate:edit.html.twig', array(
                    'form' => $form->createView(),
                    'title' => $this->trans('metatag tempelate', array(), $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

}
