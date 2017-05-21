<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\SeoRedirect;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;


class RedirectController extends BackendController {

    protected $translationDomain = 'magazine';
    public $oneItem = 'magazine';


    protected function getObjectShortName()
    {
        return 'IbtikarGlanceDashboardBundle:SeoRedirect';
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $redirects = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:SeoRedirect')->sort('createdAt', 'DESC')->getQuery()->execute();
        $redirect = new SeoRedirect();

        $form = $this->createFormBuilder($redirect, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('oldUrl', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true,'style' => 'text-align: left;direction:ltr')))
            ->add('redirectToUrl', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true,'style' => 'text-align: left;direction:ltr')))
            ->add('save', formType\SubmitType::class)
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->persist($redirect);
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return new JsonResponse(array('status' => 'redirect', 'url' => $request->getUri()));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Redirect:create.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('Add new redirect', array(), $this->translationDomain),
                'redirects' => $redirects,
                'translationDomain' => $this->translationDomain
        ));
    }
    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function batchInsertAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $redirects = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:SeoRedirect')->sort('createdAt', 'DESC')->getQuery()->execute();

        $form = $this->createFormBuilder(null, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('batch_text', formType\TextareaType::class, array('required' => true, 'attr' => array('data-validate-element' => true,'style' => 'text-align: left;direction:ltr')))
            ->add('save', formType\SubmitType::class)
            ->getForm();

        if ($request->getMethod() === 'POST') {

            foreach(explode(PHP_EOL, $request->get('form')['batch_text']) as $redirectLine){
                $redirectResult = explode(',', $redirectLine);
                $redirect = new SeoRedirect();
                $redirect->setOldUrl($redirectResult[0]);
                $redirect->setRedirectToUrl($redirectResult[1]);
                $dm->persist($redirect);
            }

            $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return new JsonResponse(array('status' => 'redirect', 'url' => $request->getUri()));
        }

        return $this->render('IbtikarGlanceDashboardBundle:Redirect:create.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('Add new redirect', array(), $this->translationDomain),
                'redirects' => $redirects,
                'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $redirect = $dm->getRepository('IbtikarGlanceDashboardBundle:SeoRedirect')->find($id);

        if (!$redirect) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $form = $this->createFormBuilder($redirect, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('oldUrl', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true,'style' => 'text-align: left;direction:ltr')))
            ->add('redirectToUrl', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true,'style' => 'text-align: left;direction:ltr')))
            ->add('save', formType\SubmitType::class)
            ->getForm();

        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_redirect_create')));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Redirect:edit.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('Edit redirect', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));

    }

    public function editMagazinePageAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $page = $dm->getRepository('IbtikarGlanceDashboardBundle:Page')->find($id);
        if (!$page) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $menus = array(array('type' => 'create', 'active' => FALSE, 'linkType' => 'add', 'title' => 'Add new magazine', 'link' => $this->generateUrl('ibtikar_glance_dashboard_magazine_create')),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Magazine'), array('type' => 'list', 'active' => TRUE, 'linkType' => 'list', 'title' => 'Edit Magazine section page', 'link' => $this->generateUrl('ibtikar_glance_dashboard_magazine_editPage', array('id' => $id)))
        );
        $breadCrumbArray = $this->preparedMenu($menus);


        $form = $this->createFormBuilder($page, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('brief', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
            ->add('briefEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
            ->add('url', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true)))
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
        return $this->render('IbtikarGlanceDashboardBundle:Magazine:editPage.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'title' => $this->trans('Edit Magazine section page', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }
}
