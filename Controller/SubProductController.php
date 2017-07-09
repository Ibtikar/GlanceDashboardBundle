<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\SubProduct;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;

class SubProductController extends BackendController {

    protected $translationDomain = 'subproduct';
    public $oneItem = 'subproduct';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "nameEn" => array(),
//            "description" => array(),
//            "descriptionEn" => array(),
            "profilePhoto" => array("type"=>"refereceImage",'isSortable'=>FALSE),
//            "createdAt" => array("type"=>"date"),
//            "updatedAt"=> array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "name",
            "nameEn",
//            "description",
//            "descriptionEn",
            'profilePhoto',
//            'createdAt',
//            "updatedAt"
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }

    protected function configureListParameters(Request $request) {
          $id = $request->get('id');
        if (!$id) {
            throw $this->createNotFoundException();
        }

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder("IbtikarGlanceDashboardBundle:SubProduct")->field('product')->equals(new \MongoId($id))
                        ->field('deleted')->equals(false);
        $this->listViewOptions->setDefaultSortBy("updatedAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array ("Edit","Delete"));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setListQueryBuilder($queryBuilder);

        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Product:view.html.twig");

    }

    protected function doList(Request $request) {
        $renderingParams = parent::doList($request);
        $id = $request->get('id');
        if (!$id) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $product = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->findOneById($id);
        if (!$product) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $medias = $dm->getRepository('IbtikarGlanceDashboardBundle:media')->findBy(array('product'=>$product->getId()));
        $coverPhotos=array();
        $activityPhotos=array();
        $relatedTips=array();
        $relatedArticles=array();
        foreach ($medias as $media) {
            if($media->getCoverPhoto()){
            if ($media->getType() == 'image') {

                $coverPhotos [] = array(
                    'type' => $media->getType(),
                    'img' => '/'.$media->getWebPath(),
                    'caption' =>  $media->getCaptionAr(),
                    'captionEn' =>  $media->getCaptionEn(),
                );
            } else {

                $coverPhotos [] = array(
                    'type' => $media->getType(),
                    'videoCode' => $media->getVid(),
                    'caption' =>  $media->getCaptionAr(),
                    'captionEn' =>  $media->getCaptionEn() ,
                );
            }
              continue;
            }
            if ($media->getActivityPhoto()) {
                if ($media->getType() == 'image') {

                    $activityPhotos [] = array(
                        'type' => $media->getType(),
                        'img' => '/' . $media->getWebPath(),
                        'caption' => $media->getCaptionAr(),
                        'captionEn' => $media->getCaptionEn(),
                    );
                } else {

                    $activityPhotos [] = array(
                        'type' => $media->getType(),
                        'videoCode' => $media->getVid(),
                        'caption' => $media->getCaptionAr(),
                        'captionEn' => $media->getCaptionEn(),
                    );
                }
                continue;
            }
        }
        
        
        foreach ($product->getRelatedArticle() as $relatedArticle) {
            $relatedArticles[] = array(
                'title' => $relatedArticle->getTitle(),
                'titleEn' => $relatedArticle->getTitleEn(),
                'img' => $relatedArticle->getCoverPhoto() ? $relatedArticle->getCoverPhoto()->getType() == 'image' ? '/' . $relatedArticle->getCoverPhoto()->getWebPath():'https://i.ytimg.com/vi/' . $relatedArticle->getCoverPhoto()->getVid().'/hqdefault.jpg' : '',

                'url' =>  $this->generateUrl('ibtikar_goody_frontend_'.trim($relatedArticle->getType()).'_view', array('slug' => $relatedArticle->getSlug()), true)

            );
        }


        foreach ($product->getRelatedTip() as $relatedTip) {
            $relatedTips[] = array(
                'title' => $relatedTip->getTitle(),
                'titleEn' => $relatedTip->getTitleEn(),
                'img' => $relatedTip->getCoverPhoto() ? $relatedTip->getCoverPhoto()->getType() == 'image' ? '/' . $relatedTip->getCoverPhoto()->getWebPath():'https://i.ytimg.com/vi/' . $relatedTip->getCoverPhoto()->getVid().'/hqdefault.jpg' : '',

                'url' =>  $this->generateUrl('ibtikar_goody_frontend_'.trim($relatedTip->getType()).'_view', array('slug' => $relatedTip->getSlug()), true)

            );
        }

        $renderingParams['product']= $product;
        $renderingParams['coverPhotos']= $coverPhotos;
        $renderingParams['activityPhotos']= $activityPhotos;
        $renderingParams['relatedTips']= $relatedTips;
        $renderingParams['relatedArticles']= $relatedArticles;

        return $renderingParams;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $profileImage=NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                'type' => 'image',
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'subproduct' => null,
                'product' => null,
                'collectionType' => 'SubProduct'
            ));
         foreach ($images as $image){
                   if($image->getProfilePhoto()){
                       $profileImage= $image;
                        continue;

                }
                }
        $product='';
        if($request->get('productId')){
            $product = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($request->get('productId'));
        }
        if(!$product){
            throw $this->createNotFoundException();
        }
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct','link'=>  $this->generateUrl('ibtikar_glance_dashboard_subproduct_create',array('productId'=>$product->getId())))
//            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list job')
            );
        $breadCrumbArray = $this->preparedMenu($menus);
        $subProduct = new SubProduct();
        $form = $this->createFormBuilder($subProduct, array('translation_domain' => $this->translationDomain,'attr'=>array('class'=>'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('name',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 2)))
                ->add('nameEn',formType\TextType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 2)))
//                ->add('product', \Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType::class,array('required' => TRUE,
//                'class' => 'IbtikarGlanceDashboardBundle:Product', 'placeholder' => $this->trans('Choose product',array(),'subproduct'),
//                'attr' => array('class' => 'select', 'data-error-after-selector' => '.select2-container')
//        ))
                ->add('description',  formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 5)))
                ->add('descriptionEn',formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 5)))
                ->add('weight', formType\NumberType::class, array('scale' => 3,'required' => FALSE,'attr' => array()))
                ->add('size',formType\NumberType::class, array('scale' => 3,'required' => FALSE,'attr' => array()))
                ->add('save', formType\SubmitType::class)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $subProduct->setProduct($product);
                $dm->persist($subProduct);
                $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                    'type' => 'image',
                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'product' => null,
                    'subproduct' => null,
                    'collectionType' => 'SubProduct'
                ));
                if (count($images) > 0) {

                    $firstImg = $images[0];

                    $this->oldDir = $firstImg->getUploadRootDir();
                    $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $subProduct->getId();
                    if (!file_exists($newDir)) {
                        @mkdir($newDir);
                    }
                }
                foreach ($images as $image) {
                    $oldFilePath = $this->oldDir . "/" . $image->getPath();
                    $newFilePath = $newDir . "/" . $image->getPath();
                    @rename($oldFilePath, $newFilePath);
                    if ($image->getProfilePhoto()) {
                        $subProduct->setProfilePhoto($image);
                        $image->setSubproduct($subProduct);
                        continue;
                    }
                }

                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:SubProduct:create.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'profileImage' => $profileImage,
                'deletePopoverConfig'=>array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('Add new subProduct', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request,$id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        //prepare form
        $subproduct = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->find($id);
        if (!$subproduct) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct','link'=>  $this->generateUrl('ibtikar_glance_dashboard_subproduct_create',array('productId'=>$subproduct->getProduct()->getId()))),
//            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list job')
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $form = $this->createFormBuilder($subproduct, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
            ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
//                ->add('product', \Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType::class,array('required' => TRUE,
//                'class' => 'IbtikarGlanceDashboardBundle:Product', 'placeholder' => $this->trans('Choose product',array(),'subproduct'),
//                'attr' => array('class' => 'select', 'data-error-after-selector' => '.select2-container')
//        ))
            ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
            ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
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

        return $this->render('IbtikarGlanceDashboardBundle:SubProduct:edit.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'profileImage' => $subproduct->getProfilePhoto(),
                'deletePopoverConfig'=>array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('edit subProduct', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }


}
