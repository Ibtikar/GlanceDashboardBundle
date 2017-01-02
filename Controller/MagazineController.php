<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Magazine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;


class MagazineController extends BackendController {

    protected $translationDomain = 'magazine';
    public $oneItem = 'magazine';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "coverPhoto" => array("type" => "refereceImage", 'isSortable' => FALSE),
            "createdAt" => array("type" => "date"),
            "updatedAt" => array("type" => "date"),
            "createdBy" => array("isSortable" => false),
            "updatedBy" => array("isSortable" => false)
        );
        $this->defaultListColumns = array(
            "name",
            'coverPhoto',
            'createdAt'
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }

    protected function configureListParameters(Request $request)
    {
        $this->listViewOptions->setDefaultSortBy("updatedAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array( "Edit","Delete","Publish"));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Magazine:list.html.twig");
    }

    protected function doList(Request $request)
    {
        $configParams = parent::doList($request);
        $configParams['deleteMsgConditionAttr'] = 'subproductNo';
        $dm = $this->get('doctrine_mongodb')->getManager();
        $configParams['magazinePage'] = $dm->getRepository('IbtikarGlanceDashboardBundle:Page')->findOneByTitle('magazine page');
        $configParams['conditionalDeletePopoverConfig'] = array(
            "question" => "Cant deleted,it contain subproduct",
            "translationDomain" => $this->translationDomain
        );
        return $configParams;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $coverImage = NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'type' => 'image',
            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
            'subproduct' => null,
            'product' => null,
            'recipe' => null,
            'magazine' => null,
            'collectionType' => 'Magazine'
        ));
        foreach ($images as $image) {
            if ($image->getCoverPhoto()) {
                $coverImage = $image;
                continue;
            }
        }
        $magazinePage = $dm->getRepository('IbtikarGlanceDashboardBundle:Page')->findOneByTitle('magazine page');

        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new magazine', 'link' => $this->generateUrl('ibtikar_glance_dashboard_magazine_create')),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Magazine'), array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'Edit Magazine section page', 'link' => $this->generateUrl('ibtikar_glance_dashboard_magazine_editPage', array('id' => $magazinePage->getId())))
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $magazine = new Magazine();
        if ($coverImage) {
            $magazine->setCoverPhoto($coverImage);
        }
        $form = $this->createFormBuilder($magazine, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('link', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
            ->add('defaultCoverPhoto', formType\HiddenType::class, array('required' => true, 'attr' => array('data-msg-required' => ' '), 'mapped' => FALSE))
            ->add('save', formType\SubmitType::class)
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->persist($magazine);
                $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                    'type' => 'image',
                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'product' => null,
                    'subproduct' => null,
                    'recipe' => null,
                    'magazine' => null,
                    'collectionType' => 'Magazine'
                ));
                if (count($images) > 0) {

                    $firstImg = $images[0];

                    $this->oldDir = $firstImg->getUploadRootDir();
                    $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $magazine->getId();
                    if (!file_exists($newDir)) {
                        @mkdir($newDir);
                    }
                }
                foreach ($images as $image) {
                    $oldFilePath = $this->oldDir . "/" . $image->getPath();
                    $newFilePath = $newDir . "/" . $image->getPath();
                    @rename($oldFilePath, $newFilePath);
                    if ($image->getCoverPhoto()) {
                        $magazine->SetCoverPhoto($image);
                        $image->setMagazine($magazine);
                        continue;
                    }
                }

                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Magazine:create.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'coverImage' => $coverImage,
                'title' => $this->trans('Add new magazine', array(), $this->translationDomain),
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
        $magazine = $dm->getRepository('IbtikarGlanceDashboardBundle:Magazine')->find($id);
        if (!$magazine) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $magazinePage = $dm->getRepository('IbtikarGlanceDashboardBundle:Page')->findOneByTitle('magazine page');
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new magazine', 'link' => $this->generateUrl('ibtikar_glance_dashboard_magazine_create')),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Magazine'), array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'Edit Magazine section page', 'link' => $this->generateUrl('ibtikar_glance_dashboard_magazine_editPage', array('id' => $magazinePage->getId())))
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $form = $this->createFormBuilder($magazine, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('link', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
            ->add('defaultCoverPhoto', formType\HiddenType::class, array('required' => true, 'attr' => array('data-msg-required' => ' '), 'mapped' => FALSE))
            ->add('save', formType\SubmitType::class)
            ->getForm();

        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Magazine:edit.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'coverImage' => $magazine->getCoverPhoto(),
                'title' => $this->trans('edit magazine', array(), $this->translationDomain),
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
