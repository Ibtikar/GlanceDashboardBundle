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

class ProductController extends BackendController {

    protected $translationDomain = 'product';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "nameEn" => array(),
            "profilePhoto" => array("type"=>"refereceImage",'isSortable'=>FALSE),
            "subproductNo" => array('type' => 'number'),
            "slug" => array('type' => 'slug'),
            "createdAt" => array("type"=>"date"),
            "updatedAt"=> array("type"=>"date"),
            "createdBy" => array("isSortable" => false),
            "updatedBy" => array("isSortable" => false)

        );
        $this->defaultListColumns = array(
            "name",
            "nameEn",
//            "description",
//            "descriptionEn",
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
        $this->listViewOptions->setActions(array ("Edit","Delete","ViewOne"));
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
        $coverVideo = NULL;
        $bannerImage = NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => null,
                'subproduct' => null,
                'collectionType' => 'Product'
            ));
         foreach ($images as $media) {
            if ($media->getType() == 'image') {
                if ($media->getCoverPhoto()) {
                    $coverImage = $media;
                    continue;
                }
                if ($media->getBannerPhoto()) {
                    $bannerImage = $media;
                    continue;
                }
                if ($media->getProfilePhoto()) {
                    $profileImage = $media;
                    continue;
                }
            } elseif ($media->getType() == 'video' && $media->getCoverPhoto()) {
                $coverVideo = $media;
            }
        }
        $product = new Product();
        $form = $this->createFormBuilder($product, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('coverType', formType\ChoiceType::class, array('choices' => Product::$coverTypeChoices, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#product_type_coverType')))
            ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('bannerUrl', formType\TextType::class, array('required' => FALSE))
//            ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
//            ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
            ->add('about', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
            ->add('aboutEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))

            ->add('video', formType\TextType::class, array('mapped'=>FALSE,'required'=>FALSE,'attr'=>array('data-rule-youtube'=>'data-rule-youtube')))
//            ->add('relatedRecipe', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
//                'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related', 'ajax-url-var' => 'relatedMaterialSearchUrl')
//            ))
//            ->add('related', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
            ->add('relatedTip', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
                'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related_tip', 'ajax-url-var' => 'relatedTipSearchUrl')
            ))
            ->add('relatedArticle', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
                'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related_article', 'ajax-url-var' => 'relatedArticleSearchUrl')))
            ->add('related_tip', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
            ->add('related_article', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
            ->add('minimumRelatedRecipe', formType\HiddenType::class, array('required' => true, 'attr' => array('data-msg-required' => ' '), 'mapped' => FALSE))

            ->add('submitButton', formType\HiddenType::class, array('required' => FALSE, "mapped" => false))
            ->add('save', formType\SubmitType::class)
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $request->get('form');

//                if ($formData['related']) {
//                    $this->updateRelatedRecipe($product, $formData['related'], $dm,'recipe');
//                }
                if ($formData['related_article']) {
                    $this->updateRelatedRecipe($product, $formData['related_article'], $dm,'article');
                }

                if ($formData['related_tip']) {
                    $this->updateRelatedRecipe($product, $formData['related_tip'], $dm,'tip');
                }

                $dm->persist($product);
                $this->slugifier($product);
                $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
//                    'type' => 'image',
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
                    if ($image->getBannerPhoto()) {
                        $product->setBannerPhoto($image);
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
                'bannerImage' => $bannerImage,
                'coverVideo' => $coverVideo,
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
        $bannerImage=$product->getBannerPhoto();

        $coverImage = NULL;
        $coverVideo = NULL;

        $mediaList = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'product' => $product->getId(),
            'collectionType' => 'Product',
            'coverPhoto' => true
        ));

        foreach ($mediaList as $media){
                if($media->getType() == 'image'){
                       $coverImage= $media;
                        continue;
                }

                if($media->getType() == 'video'){
                       $coverVideo= $media;
                        continue;
                }
        }

       $form = $this->createFormBuilder($product, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
            ->add('coverType', formType\ChoiceType::class, array('choices' => Product::$coverTypeChoices, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#product_type_coverType')))
            ->add('video', formType\TextType::class, array('mapped'=>FALSE,'required'=>FALSE,'attr'=>array('data-rule-youtube'=>'data-rule-youtube')))
            ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
            ->add('bannerUrl', formType\TextType::class, array('required' => FALSE))

//               ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
//            ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))

            ->add('about', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
            ->add('aboutEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))

//           ->add('relatedRecipe', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
//                'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related', 'ajax-url-var' => 'relatedMaterialSearchUrl')
//            ))
//            ->add('related', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
            ->add('relatedTip', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
                'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related_tip', 'ajax-url-var' => 'relatedTipSearchUrl')
            ))
            ->add('relatedArticle', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
                'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related_article', 'ajax-url-var' => 'relatedArticleSearchUrl')))
            ->add('related_tip', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
            ->add('related_article', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
            ->add('minimumRelatedRecipe', formType\HiddenType::class, array('required' => true, 'attr' => array('data-msg-required' => ' '), 'mapped' => FALSE))
            ->add('submitButton', formType\HiddenType::class, array('required' => FALSE, "mapped" => false))
            ->add('save', formType\SubmitType::class)
            ->getForm();


        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('form');
//                      if ($formData['related']) {
//                    $this->updateRelatedRecipe($product, $formData['related'], $dm,'recipe');
//                }
                if ($formData['related_article']) {
                    $this->updateRelatedRecipe($product, $formData['related_article'], $dm,'article');
                }

                if ($formData['related_tip']) {
                    $this->updateRelatedRecipe($product, $formData['related_tip'], $dm,'tip');
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


        return $this->render('IbtikarGlanceDashboardBundle:Product:edit.html.twig', array(
                'form' => $form->createView(),
                'breadcrumb' => $breadCrumbArray,
                'product' => $product,
                'profileImage' => $profileImage,
                'bannerImage' => $bannerImage,
                'coverVideo' => $coverVideo,
                'coverImage' => $coverImage,
                'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                'title' => $this->trans('edit Product', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

    protected function postDelete($ids) {

        if(!is_array($ids)){
            $ids = array($ids);
        }

        $dm = $this->get('doctrine_mongodb')->getManager();

        $subProducts = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:SubProduct')
                ->remove()
                ->field('product')->in($ids)
                ->getQuery()
                ->execute();
    }

    public function slugifier($product) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($product->getName(),100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($product->getNameEn(),100));

        $product->setSlug($slugAr);
        $product->setSlugEn($slugEn);

        $slug = new Slug();
        $slug->setReferenceId($product->getId());
        $slug->setType(Slug::$TYPE_PRODUCT);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);
        $dm->flush();
    }

    public function relatedMaterialDeleteAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $parent = $request->get('parent');
        $child = $request->get('child');

        $materialParent = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($parent);
        $materialChild = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($child);


        $contentType = $materialChild->getType();
        $removeMethod = "removeRelated".ucfirst($contentType);
        $getMethod = "getRelated".ucfirst($contentType);
        if($materialParent && $materialChild && $materialParent->$getMethod()->contains($materialChild)){
            $materialParent->$removeMethod($materialChild);
            $dm->flush();
        }
            $response = array('status' => 'success','message' => $this->trans('done sucessfully'));


        return new JsonResponse($response);

    }

    public function relatedMaterialAddAction(Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $parent = $request->get('parent');
        $child = $request->get('child');

        $materialParent = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($parent);
        $materialChild = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($child);


        $contentType = $materialChild->getType();
        $addMethod = "addRelated" . ucfirst($contentType);
        $getMethod = "getRelated" . ucfirst($contentType);
        if ($this->validToRelate($materialChild, $materialParent) && count($materialParent->$getMethod()) < 10) {

            $materialParent->$addMethod($materialChild);
            $dm->flush();
        }
        $response = array('status' => 'success', 'message' => $this->trans('done sucessfully'));


        return new JsonResponse($response);
    }

    public function validToRelate($relatedRecipe, $document) {
        $getMethod = "getRelated".ucfirst($relatedRecipe->getType());
        if($relatedRecipe){
            if(is_null($document->$getMethod()) || is_array($document->$getMethod())){
                return true;
            }elseif(is_object($document->$getMethod()) && !$document->$getMethod()->contains($relatedRecipe)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }
}
