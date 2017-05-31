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

    protected $translationDomain = 'product';

    public function editAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $banners = $dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->findAll();
        if (count($banners) == 0) {
            $banner = new HomeBanner();
            $bannerImage = '';
        } else {
            $banner = $banners[0];
            $bannerImage = $banner->getBannerPhoto();
        }


        $mediaList = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'homebanner' => $banner->getId(),
            'collectionType' => 'HomeBanner',
        ));


        $form = $this->createFormBuilder($banner, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('show', formType\CheckboxType::class, array('required' => FALSE, 'attr' => array('class' => 'styled')))
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
}
