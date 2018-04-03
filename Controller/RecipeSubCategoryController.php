<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\RecipeSubCategory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;

class RecipeSubCategoryController extends BackendController {

    protected $translationDomain = 'recipeTag';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "nameEn" => array(),
            "createdAt" => array("type"=>"date"),
            "updatedAt"=> array("type"=>"date"),
            "createdBy" => array("isSortable" => false),
            "updatedBy" => array("isSortable" => false)
        );
        $this->defaultListColumns = array(
            "name",
            "nameEn",
            "createdAt",
            "updatedAt"
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }

    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setDefaultSortBy("updatedAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array ("Edit"));
        $this->listViewOptions->setBulkActions(array());
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:RecipeSubCategory:list.html.twig");

    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request) {
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new RecipeSubCategory'), array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list tags'));
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();

        $tag = new RecipeSubCategory();
        $form = $this->createFormBuilder($tag, array('translation_domain' => $this->translationDomain,'attr'=>array('class'=>'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('name',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-unique' => 'ibtikar_glance_dashboard_recipetag_check_field_unique','data-name'=>'name','data-msg-unique'=>  $this->trans('not valid'),'data-url'=>$this->generateUrl('ibtikar_glance_dashboard_recipetag_check_field_unique'))))
                ->add('nameEn',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-unique' => 'ibtikar_glance_dashboard_recipetag_check_field_unique','data-name'=>'nameEn','data-msg-unique'=>  $this->trans('not valid'),'data-url'=>$this->generateUrl('ibtikar_glance_dashboard_recipetag_check_field_unique'))))
                ->add('slug',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-unique' => 'ibtikar_glance_dashboard_recipetag_check_field_unique','data-name'=>'nameEn','data-msg-unique'=>  $this->trans('not valid'),'data-url'=>$this->generateUrl('ibtikar_glance_dashboard_recipetag_check_field_unique'))))
                ->add('metaTagTitleAr', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagTitleEn', formType\TextType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('metaTagDesciptionAr', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('metaTagDesciptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))

                ->add('save', formType\SubmitType::class)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->persist($tag);
                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:RecipeSubCategory:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb'=>$breadCrumbArray,
                    'title'=>$this->trans('Add new RecipeSubCategory',array(),  $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request,$id) {
        $menus = array(array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list subcategories'));
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();
        //prepare form
        $subcategory = $dm->getRepository('IbtikarGlanceDashboardBundle:RecipeSubCategory')->find($id);
        if (!$subcategory) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        if (!$subcategory->getMetaTagTitleAr()) {
            $subcategory->setMetaTagTitleAr($subcategory->getName());
        }

        if (!$subcategory->getMetaTagTitleEn()) {
            $subcategory->setMetaTagTitleEn($subcategory->getNameEn());
        }

        $form = $this->createFormBuilder($subcategory, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
//                ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element'=>true, 'data-rule-unique' => 'ibtikar_glance_dashboard_recipetag_check_field_unique', 'data-name' => 'name', 'data-msg-unique' => $this->trans('not valid'), 'data-rule-maxlength' => 150, 'data-url' => $this->generateUrl('ibtikar_glance_dashboard_recipetag_check_field_unique'))))
//                ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element'=>true, 'data-rule-unique' => 'ibtikar_glance_dashboard_recipetag_check_field_unique', 'data-name' => 'nameEn', 'data-msg-unique' => $this->trans('not valid'), 'data-rule-maxlength' => 150, 'data-url' => $this->generateUrl('ibtikar_glance_dashboard_recipetag_check_field_unique'))))
//                ->add('slug',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-unique' => 'ibtikar_glance_dashboard_recipetag_check_field_unique','data-name'=>'nameEn','data-msg-unique'=>  $this->trans('not valid'),'data-url'=>$this->generateUrl('ibtikar_glance_dashboard_recipetag_check_field_unique'))))
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

                return $this->redirect($request->getUri());
            }
        }

        //return template
        return $this->render('IbtikarGlanceDashboardBundle::formLayout.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb'=>$breadCrumbArray,
                    'title'=>$subcategory->getName(),
                    'translationDomain' => $this->translationDomain
        ));
    }


    public function slugifier($tag) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($tag->getName(),100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($tag->getNameEn(),100));

        $tag->setSlug($slugAr);
        $tag->setSlugEn($slugEn);

        $dm->flush();
    }


}
