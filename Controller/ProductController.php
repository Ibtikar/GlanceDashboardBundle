<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;

class ProductController extends BackendController {

    protected $translationDomain = 'product';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "nameEn" => array(),
            "description" => array(),
            "descriptionEn" => array(),
            "profilePhoto" => array("type"=>"refereceImage",'isSortable'=>FALSE),
            "subproductNo" => array('type' => 'number'),
            "createdAt" => array("type"=>"date"),
            "updatedAt"=> array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "name",
            "nameEn",
            "description",
            "descriptionEn",
            'profilePhoto',
            'subproductNo',
            'createdAt',
            "updatedAt"
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }

    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setDefaultSortBy("updatedAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array ("Edit","Delete"));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Product:list.html.twig");

    }

    protected function doList(Request $request) {
        $configParams = parent::doList($request);
        $configParams['deleteMsgConditionAttr'] = 'subproductNo';
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
    public function createAction(Request $request) {
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Product'),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Product')
            );
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $profileImage=NULL;
        $coverImage=NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                'type' => 'image',
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => null,
                'subproduct' => null,
                'collectionType' => 'Product'
            ));
         foreach ($images as $image){
                    if($image->getCoverPhoto()){
                       $coverImage= $image;
                        continue;

                }
                if($image->getProfilePhoto()){
                       $profileImage= $image;
                        continue;

                }
                }
        $product = new Product();
        $form = $this->createFormBuilder($product, array('translation_domain' => $this->translationDomain,'attr'=>array('class'=>'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('name',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('nameEn',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('description',  formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
                ->add('descriptionEn',formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
                ->add('submitButton', formType\HiddenType::class, array('required' => FALSE, "mapped" => false))
                ->add('save', formType\SubmitType::class)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $request->get('form');

                $dm->persist($product);
                $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                    'type' => 'image',
                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'product' => null,
                    'collectionType' => 'Product'
                ));
                if (count($images) > 0) {

                    $firstImg = $images[0];

                    $this->oldDir = $firstImg->getUploadRootDir();
                    $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $product->getId();
                    if (!file_exists($newDir)) {
                        @mkdir($newDir);
                    }
                }
                foreach ($images as $image) {
                    $oldFilePath = $this->oldDir . "/" . $image->getPath();
                    $newFilePath = $newDir . "/" . $image->getPath();
                    @rename($oldFilePath, $newFilePath);
                    if ($image->getCoverPhoto()) {
                        $product->setCoverPhoto($image);
                        $image->setProduct($product);
                        continue;
                    }
                    if ($image->getProfilePhoto()) {
                        $product->setProfilePhoto($image);
                        $image->setProduct($product);
                        continue;
                    }
                }

                $dm->flush();

            $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                if ($formData['submitButton'] == 'add_save') {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_subproduct_create', array('productId' => $product->getId()))));
                } else {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_product_list'), array(), true));
                }
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Product:create.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'profileImage' => $profileImage,
                'coverImage' => $coverImage,
                'deletePopoverConfig'=>array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('Add new Product', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request,$id) {
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Product'),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Product')
            );
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();

        $product = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($id);
        if (!$product) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $profileImage=$product->getProfilePhoto();
        $coverImage=$product->getCoverPhoto();

       $form = $this->createFormBuilder($product, array('translation_domain' => $this->translationDomain,'attr'=>array('class'=>'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('name',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('nameEn',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('description',  formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
                ->add('descriptionEn',formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
                ->add('submitButton', formType\HiddenType::class, array('required' => FALSE, "mapped" => false))
                ->add('save', formType\SubmitType::class)
                ->getForm();


        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('form');
                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                if ($formData['submitButton'] == 'add_save') {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_subproduct_create', array('productId' => $product->getId()))));
                } else {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_product_list'), array(), true));
                }
            }
        }


        return $this->render('IbtikarGlanceDashboardBundle:Product:edit.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'profileImage' => $profileImage,
                'coverImage' => $coverImage,
                'deletePopoverConfig'=>array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('edit Product', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }


    protected function postDelete($ids) {

        if(!is_array($ids)){
            $ids = array($ids);
        }

        $dm = $this->get('doctrine_mongodb')->getManager();

        $subProducts = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Subproduct')
                ->remove()
                ->field('product')->in($ids)
                ->getQuery()
                ->execute();
    }
}
