<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Ibtikar\GlanceDashboardBundle\Document\Related;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\History;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Ibtikar\GlanceDashboardBundle\Form\Type\ProductType;
use Ibtikar\GlanceDashboardBundle\Document\SubProduct;

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
        $this->listViewOptions->setActions(array ("Edit","Delete","ViewOne",'Order'));
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

                if ($media->getBannerPhoto()) {
                    $bannerImage = $media;
                    continue;
                }
                if ($media->getProfilePhoto()) {
                    $profileImage = $media;
                    continue;
                }
            }
        }
        $product = new Product();
        $form = $this->createForm(\Ibtikar\GlanceDashboardBundle\Form\Type\ProductType::class,$product, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));


        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $request->get('product');


                $dm->persist($product);
//                $ids=array();
//                $relatedArray = json_decode($formData['related_article'], true);
//
//                foreach($relatedArray as $relatedArticle){
//                    $ids[]=$relatedArticle['id'];
//                }
//                $relatedArray = json_decode($formData['related_tip'], true);
//
//                foreach($relatedArray as $relatedArticle){
//                    $ids[]=$relatedArticle['id'];
//                }
//                $recipes=$dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('id'=>array('$in'=>$ids)));
//                $recipeArray=array();
//                foreach($recipes as $recipe){
//                    $recipeArray[$recipe->getId()]=$recipe;
//
//                }
//                if ($formData['related_article']) {
//                    $this->updateRelatedRecipe($product, $formData['related_article'], $dm,'article',$recipeArray);
//                }
//
//                if ($formData['related_tip']) {
//                    $this->updateRelatedRecipe($product, $formData['related_tip'], $dm,'tip',$recipeArray);
//                }
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
                $this->get('history_logger')->log($product, History::$ADD);

                $dm->flush();
                $this->updateMaterialGallary($product, $formData['media'], $dm);

            $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                if ($formData['submitButton'] == 'add_save') {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_subproduct_create', array('productId' => $product->getId()))));
                } elseif ($formData['submitButton'] == 'dev-save-add-activity') {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $product->getId()))));
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

        foreach ($mediaList as $media) {
            if ($media->getType() == 'image') {
                $coverImage = $media;
                continue;
            }

            if ($media->getType() == 'video') {
                $coverVideo = $media;
                continue;
            }
        }
        $flushObject = FALSE;
        $relatedArticle = $product->getRelatedArticle();
        if (count($relatedArticle) == 0) {
            $relatedExist = $dm->getRepository('IbtikarGlanceDashboardBundle:Related')->findBy(array('product' => $product->getId(), 'type' => Recipe::$types['article']));
            if (count($relatedExist) > 0) {
                foreach ($relatedExist as $related) {
                    $product->addRelatedArticle($related->getRecipe());
                }
                $flushObject = TRUE;
            }
        }
        $relatedTip = $product->getRelatedTip();

        if (count($relatedTip) == 0) {
            $relatedExist = $dm->getRepository('IbtikarGlanceDashboardBundle:Related')->findBy(array('product' => $product->getId(), 'type' => Recipe::$types['tip']));
            if (count($relatedExist) > 0) {
                foreach ($relatedExist as $related) {
                    $product->addRelatedTip($related->getRecipe());
                }
                $flushObject = TRUE;
            }
        }
        if ($flushObject) {
            $dm->flush();
        }
        $form = $this->createForm(\Ibtikar\GlanceDashboardBundle\Form\Type\ProductType::class,$product, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));



        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('product');
//                $ids=array();
//                $relatedArray = json_decode($formData['related_article'], true);
//
//                foreach($relatedArray as $relatedArticle){
//                    $ids[]=$relatedArticle['id'];
//                }
//                $relatedArray = json_decode($formData['related_tip'], true);
//
//                foreach($relatedArray as $relatedArticle){
//                    $ids[]=$relatedArticle['id'];
//                }
//                $recipes=$dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('id'=>array('$in'=>$ids)));
//                $recipeArray=array();
//                foreach($recipes as $recipe){
//                    $recipeArray[$recipe->getId()]=$recipe;
//
//                }
//                if ($formData['related_article']) {
//                    $this->updateRelatedRecipe($product, $formData['related_article'], $dm,'article',$recipeArray);
//                }
//
//                if ($formData['related_tip']) {
//                    $this->updateRelatedRecipe($product, $formData['related_tip'], $dm,'tip',$recipeArray);
//                }
                $this->get('history_logger')->log($product, History::$EDIT);

                $dm->flush();
                $this->updateMaterialGallary($product, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                if ($formData['submitButton'] == 'add_save') {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_subproduct_create', array('productId' => $product->getId()))));
                } elseif ($formData['submitButton'] == 'dev-save-add-activity') {
                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $product->getId()))));
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
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($product->getName(), 100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($product->getNameEn(), 100));
        $arabicCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Product')
                ->field('deleted')->equals(FALSE)
                ->field('slug')->equals($slugAr)
                ->field('id')->notEqual($product->getId())->
                getQuery()->execute()->count();

        $englishCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Product')
                ->field('deleted')->equals(FALSE)
                ->field('slugEn')->equals($slugEn)
                ->field('id')->notEqual($product->getId())->
                getQuery()->execute()->count();
        if ($arabicCount != 0) {
            $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($product->getName(), 100) . "-" . date('ymdHis'));
        }
        if ($englishCount != 0) {
            $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($product->getNameEn(), 100) . "-" . date('ymdHis'));
        }

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
            $relatedExist = $dm->getRepository('IbtikarGlanceDashboardBundle:Related')->findOneBy(array('product' => $materialParent->getId(), 'recipe' => $materialChild->getId()));
            if ($relatedExist) {
                $relatedExist->delete($dm);
            }
            $this->get('history_logger')->log($materialParent, History::$REMOVERELATED,"remove related ".ucfirst($contentType),$materialChild );

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
            $relatedExist = $dm->getRepository('IbtikarGlanceDashboardBundle:Related')->findBy(array('product'=>$materialParent->getId(),'recipe'=>$materialChild->getId()));
            if (!$relatedExist) {
                $related = new Related();
                $related->setProduct($materialParent);
                $related->setRecipe($materialChild);
                $related->setType($materialChild->getType());
                $dm->persist($related);
            }

            $this->get('history_logger')->log($materialParent, History::$ADDRELATED,"add related ".ucfirst($contentType), $materialChild );

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

    public function updateRelatedRecipe($document,$relatedJson,$dm = null,$type='recipe',$recipes=array()) {
        if (!$dm) {
            $dm = $this->get('doctrine_mongodb')->getManager();
        }

        $array = json_decode($relatedJson, true);
        foreach($array as $relatedRecipe){
            if(isset($recipes[$relatedRecipe['id']])){
              $recipe = $recipes[$relatedRecipe['id']];
            } else {
                $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($relatedRecipe['id']);
            }
            $contentType = $recipe->getType();
            $addMethod = "addRelated".ucfirst($contentType);
            $getMethod = "getRelated".ucfirst($contentType);

            if ($this->validToRelate($recipe, $document) && count($document->$getMethod()) < 10) {
                $document->$addMethod($recipe);

                $relatedExist = $dm->getRepository('IbtikarGlanceDashboardBundle:Related')->findBy(array('product' => $document->getId(), 'recipe' => $recipe->getId()));
                if (!$relatedExist) {
                    $related = new Related();
                    $related->setProduct($document);
                    $related->setRecipe($recipe);
                    $related->setType($recipe->getType());
                    $dm->persist($related);

                }
                $dm->flush();
                $this->get('history_logger')->log($document, History::$ADDRELATED, "add related " . ucfirst($contentType) ,$recipe);


                }
        }
    }

    public function updateMaterialGallary($document, $gallary, $dm = null) {
        if (!$dm) {
            $dm = $this->get('doctrine_mongodb')->getManager();
        }

        $gallary = json_decode($gallary,true);

        if (isset($gallary[0]) && is_array($gallary[0])) {

                $imagesIds = array();
                $imagesData = array();
                foreach ($gallary as $galleryImageData) {
                    $imagesIds [] = $galleryImageData['id'];
                $imagesData[$galleryImageData['id']] = $galleryImageData;
            }
            $images = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('id' => array('$in' => $imagesIds)));
            if (count($images) > 0) {
                $firstImg = $images[0];
                $this->oldDir = $firstImg->getUploadRootDir();
                $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $document->getId();
                if (!file_exists($newDir)) {
                    @mkdir($newDir);
                }
            }
            $documentImages = 0;

            $documentImages = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('product' => $document->getId(), 'coverPhoto' => false));

            $count = count($documentImages);
            $coverExist = FALSE;

            foreach ($images as $mediaObj) {
                $image = $imagesData[$mediaObj->getId()];
                $oldFilePath = $this->oldDir . "/" . $mediaObj->getPath();
                $newFilePath = $newDir . "/" . $mediaObj->getPath();
                @rename($oldFilePath, $newFilePath);
                $mediaObj->setProduct($document);
                $mediaObj->setOrder($image['order']);
                $mediaObj->setCaptionAr($image['captionAr']);
                $mediaObj->setCaptionEn($image['captionEn']);
            }
//
            $dm->flush();
        }

        $dm->flush();
    }


    public function orderAction(Request $request,$id) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $product = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($id);
        if (!$product) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

          $form = $this->createFormBuilder($product, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('recipes', DocumentType::class, array('required' => false, 'multiple' => 'multiple', 'placeholder' => 'Choose Product', 'class' => 'IbtikarGlanceDashboardBundle:Recipe', 'query_builder' => function ( $er) use($product) {
                        return $er->createQueryBuilder('u')
                                ->field('products')->in(array($product->getId()))
                                ->field('status')->equals(Recipe::$statuses['publish'])
                                ->field('type')->equals(Recipe::$types['recipe']);
                    }, 'attr' => array('data-maximum-selection-length' => 3, 'data-img-method' => 'getDefaultCoverPhotoVideoOrImage', 'data-img-default' => 'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg', 'class' => 'select-with-thumb')))
                ->add('bestProduct', DocumentType::class, array('required' => false, 'multiple' => 'multiple', 'placeholder' => 'Choose Product', 'class' => 'IbtikarGlanceDashboardBundle:SubProduct', 'query_builder' => function ( $er)use($product) {
                        return $er->createQueryBuilder('u')
                                ->field('product')->equals($product->getId())
                                ->field('type')->equals(SubProduct::$TypeChoices['bestProduct']);
                    }, 'attr' => array('data-maximum-selection-length' => 4, 'data-img-method' => 'coverPhoto', 'data-img-default' => 'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg', 'class' => 'select-with-thumb')))
                ->add('whatHappening', DocumentType::class, array('required' => false, 'multiple' => 'multiple', 'placeholder' => 'Choose Product', 'class' => 'IbtikarGlanceDashboardBundle:SubProduct', 'query_builder' => function ( $er)use($product) {
                        return $er->createQueryBuilder('u')
                                ->field('product')->equals($product->getId())
                                ->field('type')->equals(SubProduct::$TypeChoices['activity']);
                    }, 'attr' => array('data-maximum-selection-length' => 8, 'data-img-method' => 'coverPhoto', 'data-img-default' => 'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg', 'class' => 'select-with-thumb')))
                ->add('subproduct', DocumentType::class, array('required' => false, 'multiple' => 'multiple', 'placeholder' => 'Choose Product', 'class' => 'IbtikarGlanceDashboardBundle:SubProduct', 'query_builder' => function ( $er)use($product) {
                        return $er->createQueryBuilder('u')
                                ->field('product')->equals($product->getId())
                                ->field('type')->equals('subproduct');
                    }, 'attr' => array('data-maximum-selection-length' => 6, 'data-img-method' => 'coverPhoto', 'data-img-default' => 'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg', 'class' => 'select-with-thumb')))
                ->add('save', formType\SubmitType::class)
                ->getForm();

        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {


                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                    return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_product_list'), array(), true));
            }
        }


        return $this->render('IbtikarGlanceDashboardBundle:Product:order.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->trans('dispalay content  OnProduct', array(), $this->translationDomain),
                'translationDomain' => $this->translationDomain
        ));
    }

}
