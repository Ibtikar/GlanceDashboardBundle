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

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request) {
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Product'),
//            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list job')
            );
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $profileImage=NULL;
        $coverImage=NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                'type' => 'image',
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => null,
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
                ->add('save', formType\SubmitType::class)
                ->add('description',  formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
                ->add('descriptionEn',formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
//                ->add('images',formType\TextareaType::class, array('required' => FALSE,'mapped'=>FALSE,'attr' => array('parent-class'=>'hide')))
                ->add('save', formType\SubmitType::class)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
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
                return $this->redirect($request->getUri());
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
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new job'), array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list job'));
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();
        //prepare form
        $job = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($id);
        if (!$job) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $form = $this->createFormBuilder($job, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('title', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element'=>true, 'data-rule-unique' => 'ibtikar_glance_dashboard_job_check_field_unique', 'data-name' => 'title', 'data-msg-unique' => $this->trans('not valid'), 'data-rule-maxlength' => 150, 'data-url' => $this->generateUrl('ibtikar_glance_dashboard_job_check_field_unique'))))
                ->add('titleEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element'=>true, 'data-rule-unique' => 'ibtikar_glance_dashboard_job_check_field_unique', 'data-name' => 'titleEn', 'data-msg-unique' => $this->trans('not valid'), 'data-rule-maxlength' => 150, 'data-url' => $this->generateUrl('ibtikar_glance_dashboard_job_check_field_unique'))))
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
                    'title'=>$this->trans('Edit Product',array(),  $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param Document $document
     * @return string
     */
    protected function validateDelete(Document $document) {
        if(in_array($document->getTitleEn(), Job::$systemEnglishProductTitles)) {
            return $this->get('translator')->trans('failed operation');
        }
        if ($document->getStaffMembersCount() > 0) {
            return $this->trans('Cant deleted,it contain staff members',array(),$this->translationDomain);
        }
    }

}
