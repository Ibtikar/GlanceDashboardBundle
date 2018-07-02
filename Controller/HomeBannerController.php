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

class HomeBannerController extends BackendController
{

    protected $translationDomain = 'bannar';

    public function editAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $banners = $dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->findOneBy(array('shortName'=>'HomeBanner'));
        if (count($banners) == 0) {
            $banner = new HomeBanner();
            $bannerImage = '';
        } else {
            $banner = $banners;
            $bannerImage = $banner->getBannerPhoto();
        }

        $form = $this->createFormBuilder($banner, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('show', formType\CheckboxType::class, array('required' => FALSE, 'attr' => array('class' => 'styled')))
                ->add('metaTagTitleAr', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagTitleEn', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagDesciptionAr', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('metaTagDesciptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('bannerUrl', formType\TextType::class, array('required' => FALSE))
                ->add('save', formType\SubmitType::class)
                ->getForm();


        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->persist($banner);
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }


        return $this->render('IbtikarGlanceDashboardBundle:HomeBannar:edit.html.twig', array(
                'form' => $form->createView(),
                'bannerImage' => $bannerImage,
                'id' => $banner->getId()? $banner->getId(): 'null',
                'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('edit Banner', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }


    public function editPageContentAction(Request $request,$id)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $banner = $dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->find($id);
        if(!$banner){
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        if (!$banner->getMetaTagDesciptionAr()) {
            $banner->setMetaTagDesciptionAr($banner->getDescription());
        }

        if (!$banner->getMetaTagDesciptionEn()) {
            $banner->setMetaTagDesciptionEn($banner->getDescriptionEn());
        }

        $bannerImage = $banner->getBannerPhoto();


        $form = $this->createForm(\Ibtikar\GlanceDashboardBundle\Form\Type\PageType::class, $banner, array('translation_domain' => $this->translationDomain,
                'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal','shortName' => $banner->getShortName())));




        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->persist($banner);
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }



        return $this->render('IbtikarGlanceDashboardBundle:Page:edit.html.twig', array(
                'form' => $form->createView(),
                'bannerImage' => $bannerImage,
                'page' => $banner->getShortName(),
                'id' => $banner->getId()? $banner->getId(): 'null',
                'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('edit '.$banner->getShortName(), array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }
    public function editMealCourseContentAction(Request $request,$id)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $banner = $dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->find($id);
        if(!$banner){
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        if (!$banner->getMetaTagDesciptionAr()) {
            $banner->setMetaTagDesciptionAr($banner->getDescription());
        }

        if (!$banner->getMetaTagDesciptionEn()) {
            $banner->setMetaTagDesciptionEn($banner->getDescriptionEn());
        }


        $form = $this->createForm(\Ibtikar\GlanceDashboardBundle\Form\Type\MealType::class, $banner, array('translation_domain' => $this->translationDomain,
                'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal','shortName' => $banner->getShortName())));




        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->persist($banner);
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }



        return $this->render('IbtikarGlanceDashboardBundle:Page:editMeal.html.twig', array(
                'form' => $form->createView(),
                'page' => $banner->getShortName(),
                'id' => $banner->getId()? $banner->getId(): 'null',
                'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('edit '.$banner->getShortName(), array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }



}
