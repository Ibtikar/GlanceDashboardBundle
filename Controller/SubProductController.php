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
            "profilePhoto" => array("type" => "refereceImageOrVideo", 'isSortable' => FALSE),
            "type" => array("type" => "translated"),
//            "updatedAt"=> array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "name",
            "nameEn",
            'type',
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
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct','link'=>  $this->generateUrl('ibtikar_glance_dashboard_subproduct_create',array('productId'=>$product->getId()))),
array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Activity', 'link' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $product->getId())))            );
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
        array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Activity', 'link' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $subproduct->getProduct()->getId())))        );
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


    public function getListJsonData($request, $renderingParams) {
        $documentObjects = array();
        foreach ($renderingParams['pagination'] as $document) {
            $templateVars = array_merge(array('object' => $document), $renderingParams);
            $oneDocument = array();

            foreach ($renderingParams['columnArray'] as $value) {
                if ($value == 'id') {
                    $oneDocument['id'] = '<div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="ids[]" class="styled dev-checkbox" value="' . $document->getId() . '">
                                    </label>
                              </div>';
                    continue;
                }
                if ($value == 'actions') {
                    $security = $this->container->get('security.authorization_checker');
                    if ($this->listViewOptions->hasActionsColumn($this->calledClassName)) {
                        $oneDocument['actions'] = $this->renderView('IbtikarGlanceDashboardBundle:SubProduct:_listActions.html.twig', $templateVars);
                        continue;
                    }
                }
                $getfunction = "get" . ucfirst($value);
                if ($value == 'name' && $document instanceof \Ibtikar\GlanceUMSBundle\Document\Role) {
                    $oneDocument[$value] = '<a class="dev-role-getPermision" href="javascript:void(0)" data-id="' . $document->getId() . '">' . $document->$getfunction() . '</a>';
                } elseif ($value == 'username') {
                    $image = $document->getWebPath();
                    if (!$image) {
                        $image = 'bundles/ibtikarshareeconomydashboarddesign/images/profile.jpg';
                    }
                    $oneDocument[$value] = '<div class="media-left media-middle">'
                            . '<img src="/' . $image . '" class="img-circle img-lg" alt=""></div>
                                                <div class="media-body">
                                                    <a href="javascript:void(0);" class="display-inline-block text-default text-semibold letter-icon-title">  ' . $document->$getfunction() . ' </a>
                                                </div>';
                } elseif ($value == 'answersEnabled') {
                    $oneDocument[$value] = $this->trans('answer ' . strtolower($document->$getfunction()), array(), $this->translationDomain);
                } elseif ($value == 'email' && !method_exists($document, 'get' . ucfirst($value))) {
                    $oneDocument[$value] = $this->get('app.twig.property_accessor')->propertyAccess($document, 'createdBy', $value);
                } elseif ($value == 'status' || $value == 'type') {
                    $oneDocument[$value] = $this->trans($document->$getfunction(), array(), $this->translationDomain);
                } elseif ($value == 'slug') {
                    $request->setLocale('ar');
                    $oneDocument[$value] = '<a href="' . $this->generateUrl('ibtikar_goody_frontend_view', array('slug' => $document->$getfunction()), UrlGeneratorInterface::ABSOLUTE_URL) . '" target="_blank">' . $this->generateUrl('ibtikar_goody_frontend_view', array('slug' => $document->$getfunction()), UrlGeneratorInterface::ABSOLUTE_URL) . ' </a>';
                } elseif ($value == 'profilePhoto' || $value == 'coverPhoto') {
                    $image = $document->$getfunction();
                    if (!$image) {
                        $image = '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg';
                    } else {
                        if ($image->getType() == 'video') {
                            $image = 'https://i.ytimg.com/vi/' . $image->getVid() . '/hqdefault.jpg';
                        } else {
                            $image = '/' . $image->getWebPath();
                        }
                    }
                    $oneDocument[$value] = '<div class="thumbnail small-thumbnail"><div class="thumb thumb-slide"><img alt="" src="' . $image . '">
                            <div class="caption"><span> <a data-popup="lightbox" class="btn btn-primary btn-icon" href="' . $image . '"><i class="icon-zoomin3"></i></a>
                                </span> </div>  </div> </div>';
                } elseif ($document->$getfunction() instanceof \DateTime) {
                    $oneDocument[$value] = $document->$getfunction() ? $document->$getfunction()->format('Y-m-d') : null;
                } elseif (is_array($document->$getfunction()) || $document->$getfunction() instanceof \Traversable) {
                    $elementsArray = array();
                    foreach ($document->$getfunction() as $element) {
                        if ($value == 'course') {
                            $elementsArray[] = is_object($element) ? $element->__toString() : $this->trans($element, array(), $this->translationDomain);
                            continue;
                        }
                        $elementsArray[] = is_object($element) ? $element->__toString() : $element;
                    }
                    $oneDocument[$value] = implode(',', $elementsArray);
                } else {
                    $fieldData = $document->$getfunction();
                    $oneDocument[$value] = is_object($fieldData) ? $fieldData->__toString() : $this->getShortDescriptionString($fieldData);
                }
            }

            $documentObjects[] = $oneDocument;
        }
        $rowsHeader = $this->getColumnHeaderAndSort($request);
        return new JsonResponse(array('status' => 'success', 'data' => $documentObjects, "draw" => 0, 'sEcho' => 0, 'columns' => $rowsHeader['columnHeader'],
            "recordsTotal" => $renderingParams['total'],
            "recordsFiltered" => $renderingParams['total']));
    }

}
