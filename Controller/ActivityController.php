<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\SubProduct;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;

class ActivityController extends BackendController {

    protected $translationDomain = 'subproduct';
    public $oneItem = 'subproduct';

    protected function getObjectShortName() {
        return 'IbtikarGlanceDashboardBundle:SubProduct';
    }

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "nameEn" => array(),
//            "description" => array(),
//            "descriptionEn" => array(),
            "profilePhoto" => array("type" => "refereceImage", 'isSortable' => FALSE),
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

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder("IbtikarGlanceDashboardBundle:Subproduct")->field('product')->equals(new \MongoId($id))
                        ->field('deleted')->equals(false);
        $this->listViewOptions->setDefaultSortBy("updatedAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array("Edit", "Delete"));
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
        $medias = $dm->getRepository('IbtikarGlanceDashboardBundle:media')->findBy(array('product' => $product->getId()));
        $coverPhotos = array();
        $activityPhotos = array();
        $relatedTips = array();
        $relatedArticles = array();
        foreach ($medias as $media) {
            if ($media->getCoverPhoto()) {
                if ($media->getType() == 'image') {

                    $coverPhotos [] = array(
                        'type' => $media->getType(),
                        'img' => '/' . $media->getWebPath(),
                        'caption' => $media->getCaptionAr(),
                        'captionEn' => $media->getCaptionEn(),
                    );
                } else {

                    $coverPhotos [] = array(
                        'type' => $media->getType(),
                        'videoCode' => $media->getVid(),
                        'caption' => $media->getCaptionAr(),
                        'captionEn' => $media->getCaptionEn(),
                    );
                }
                continue;
            }
            if ($media->getSubproductPhoto()) {
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
                'img' => $relatedArticle->getCoverPhoto() ? $relatedArticle->getCoverPhoto()->getType() == 'image' ? '/' . $relatedArticle->getCoverPhoto()->getWebPath() : 'https://i.ytimg.com/vi/' . $relatedArticle->getCoverPhoto()->getVid() . '/hqdefault.jpg' : '',
                'url' => $this->generateUrl('ibtikar_goody_frontend_' . trim($relatedArticle->getType()) . '_view', array('slug' => $relatedArticle->getSlug()), true)
            );
        }


        foreach ($product->getRelatedTip() as $relatedTip) {
            $relatedTips[] = array(
                'title' => $relatedTip->getTitle(),
                'titleEn' => $relatedTip->getTitleEn(),
                'img' => $relatedTip->getCoverPhoto() ? $relatedTip->getCoverPhoto()->getType() == 'image' ? '/' . $relatedTip->getCoverPhoto()->getWebPath() : 'https://i.ytimg.com/vi/' . $relatedTip->getCoverPhoto()->getVid() . '/hqdefault.jpg' : '',
                'url' => $this->generateUrl('ibtikar_goody_frontend_' . trim($relatedTip->getType()) . '_view', array('slug' => $relatedTip->getSlug()), true)
            );
        }

        $renderingParams['product'] = $product;
        $renderingParams['coverPhotos'] = $coverPhotos;
        $renderingParams['activityPhotos'] = $activityPhotos;
        $renderingParams['relatedTips'] = $relatedTips;
        $renderingParams['relatedArticles'] = $relatedArticles;

        return $renderingParams;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $profileImage = NULL;
        $profileVideo = NULL;
        $medias = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
            'subproduct' => null,
            'activity' => null,
            'product' => null,
            'ProfilePhoto' => true,
            'collectionType' => 'Activity'
        ));
        foreach ($medias as $media) {
            if ($media->getType() == 'image') {
                if ($media->getProfilePhoto()) {
                    $profileImage = $media;
                    continue;
                }
            } elseif ($media->getType() == 'video' && $media->getProfilePhoto()) {
                $profileVideo = $media;
            }
        }
        $product = '';
        if ($request->get('productId')) {
            $product = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($request->get('productId'));
        }
        if (!$product) {
            throw $this->createNotFoundException();
        }
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct','link'=>  $this->generateUrl('ibtikar_glance_dashboard_subproduct_create',array('productId'=>$product->getId()))),array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Activity', 'link' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $product->getId())))
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $activity = new Subproduct();
        $form = $this->createFormBuilder($activity, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('type', formType\ChoiceType::class, array('choices' => SubProduct::$TypeChoices, 'expanded' => true,  'data' => SubProduct::$TypeChoices['activity'], 'attr' => array('data-error-after-selector' => '#form_coverType')))
                ->add('profileType', formType\ChoiceType::class, array('choices' => SubProduct::$profileTypeChoices, 'expanded' => true, 'attr' => array('data-error-after-selector' => '#form_coverType')))
                ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
                ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
                ->add('video', formType\TextType::class, array('mapped' => FALSE, 'required' => FALSE, 'attr' => array('data-rule-youtube' => 'data-rule-youtube')))
                ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
                ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
                ->add('url', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
                ->add('urlEn', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
                ->add('save', formType\SubmitType::class)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $request->get('form');

                $profilePhotoType = $formData['profileType'];

                $activity->setProduct($product);
                $dm->persist($activity);
                $medias = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'product' => null,
                    'subproduct' => null,
                    'activity' => null,
                    'collectionType' => 'Activity'
                ));
                foreach ($medias as $media) {
                    if ($profilePhotoType == 'image' && $media->getType() == 'image' && $media->getProfilePhoto()) {
                        $activity->setProfilePhoto($media);
                        continue;
                    }

                    if ($profilePhotoType == 'video' && $media->getType() == 'video' && $media->getProfilePhoto()) {
                        $activity->setProfilePhoto($media);
                        continue;
                    }
                }
                if (count($medias) > 0) {

                    $firstImg = $medias[0];

                    $this->oldDir = $firstImg->getUploadRootDir();
                    $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $activity->getId();
                    if (!file_exists($newDir)) {
                        @mkdir($newDir);
                    }
                }
                foreach ($medias as $media) {
                    $oldFilePath = $this->oldDir . "/" . $media->getPath();
                    $newFilePath = $newDir . "/" . $media->getPath();
                    @rename($oldFilePath, $newFilePath);
                    if ($media->getProfilePhoto()) {
                        $activity->setProfilePhoto($media);
                        $media->setActivity($activity);
                        continue;
                    }
                }

                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Activity:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'profileImage' => $profileImage,
                    'profileVideo' => $profileVideo,
                    'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                    'title' => $this->trans('Add new Activity', array(), $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request, $id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        //prepare form
        $subproduct = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->find($id);
        if (!$subproduct) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct','link'=>  $this->generateUrl('ibtikar_glance_dashboard_subproduct_create',array('productId'=>$subproduct->getProduct()->getId()))),array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Activity', 'link' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $subproduct->getProduct()->getId())))
        );
        $profileImage=NULL;
        $profileVideo=NULL;
        $medias = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'activity' => $subproduct->getId(),
            'collectionType' => 'Activity',
            'ProfilePhoto' => true
        ));
        foreach ($medias as $media) {
            if ($media->getType() == 'image') {
                if ($media->getProfilePhoto()) {
                    $profileImage = $media;
                    continue;
                }
            } elseif ($media->getType() == 'video' && $media->getProfilePhoto()) {
                $profileVideo = $media;
            }
        }

        $breadCrumbArray = $this->preparedMenu($menus);
        $form = $this->createFormBuilder($subproduct, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('type', formType\ChoiceType::class, array('choices' => SubProduct::$TypeChoices, 'expanded' => true,  'attr' => array('data-error-after-selector' => '#form_coverType')))

                ->add('profileType', formType\ChoiceType::class, array('choices' => SubProduct::$profileTypeChoices, 'expanded' => true, 'attr' => array('data-error-after-selector' => '#form_profileType')))
                ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
                ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
                ->add('video', formType\TextType::class, array('mapped' => FALSE, 'required' => FALSE, 'attr' => array('data-rule-youtube' => 'data-rule-youtube')))
                ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
                ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
                ->add('url', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
                ->add('urlEn', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
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
                $profilePhotoType = $formData['profileType'];

                $mediaList = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                    'activity' => $subproduct->getId(),
                    'collectionType' => 'Activity',
                    'ProfilePhoto' => true
                ));

                foreach ($mediaList as $media) {
                    if ($profilePhotoType == $media->getType() && $media->getProfilePhoto()) {
                        $subproduct->setProfilePhoto($media);
                        continue;
                    }
                }
                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());

            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Activity:edit.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'profileImage' => $profileImage,
                    'profileVideo' => $profileVideo,
                    'profileType' => $subproduct->getProfileType(),
                    'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                    'title' => $this->trans('edit Activity', array(), $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    public function deleteAction(Request $request) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'),'count'=>  $this->getDocumentCount());
            return new JsonResponse($result);
        }
        $id = $request->get('id');
        if (!$id) {
            return $this->getFailedResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document || $document->getDeleted()) {
            return new JsonResponse(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation'),'count'=>  $this->getDocumentCount()));

        }

        $errorMessage = $this->validateDelete($document);

        if ($errorMessage || is_null($document)) {
            return $this->getFailedAlertResponse($errorMessage);
        }

        try {
            $id = $document->getId();
            $productId=$document->getProduct()->getId();
            $document->delete($dm, $this->getUser());
//            $dm->remove($document);
            $dm->flush();
            $this->postDelete($id);
        } catch (\Exception $e) {

            return $this->getFailedResponse();
        }

        $count = $dm->createQueryBuilder($this->getObjectShortName())
                ->field('deleted')->equals(FALSE)
                ->field('product')->equals($productId)
                ->getQuery()
                ->count();

        return new JsonResponse(array('status' => 'success', 'message' => $this->get('translator')->trans('done sucessfully'),'count'=>$count));
    }

    public function bulkAction(Request $request) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }
        $ids = array_diff($request->get('ids', array()), array(""));
        if (empty($ids)) {
            return $this->getFailedResponse();
        }
        $bulkAction = $request->get('bulk-action');

        if (!$bulkAction) {
            return $this->getFailedResponse();
        }

        $successIds = array();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $documents = $dm->getRepository($this->getObjectShortName())->findBy(array('id' => array('$in' => array_values($ids))));
        $translator = $this->get('translator');
        $message = str_replace(array('%action%', '%item-translation%', '%ids-count%'), array($translator->trans($bulkAction), $this->trans(strtolower($this->oneItem != ""?$this->oneItem:$this->calledClassName)), count($ids)), $translator->trans('successfully %action% %success-count% %item-translation% from %ids-count%.'));
        $foundDocumentsIds = array();
        foreach ($documents as $document) {
            $foundDocumentsIds [] = $document->getId();
        }
        $deletedIds = array_diff($ids, $foundDocumentsIds);
        $data = array(
            'status' => 'success',
            'message' => '',
            'bulk-action' => $bulkAction,
            'success' => &$successIds,
            'errors' => array()
        );
        $data['errors'][$translator->trans('Already deleted.')] = $deletedIds;
        if (count($deletedIds) === count($ids)) {
            $data['message'] = str_replace('%success-count%', 0, $message);
            $data['count']=  $this->getDocumentCount();
            return new JsonResponse($data);
        }

        switch ($bulkAction) {
            case 'Delete':

                $permission = 'ROLE_' . strtoupper($this->calledClassName) . '_DELETE';

                if (!$securityContext->isGranted($permission) && !$securityContext->isGranted('ROLE_ADMIN')) {
                    $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'),'count'=>  $this->getDocumentCount());
                    return new JsonResponse($result);
                }

                $bulkQueries = array(
                    'readLater' => array(),
                    'likes' => array()
                );

                foreach ($documents as $document) {
                    $productId=$document->getProduct()->getId();
                    $errorMessage = $this->validateDelete($document);
                    if ($document->getNotModified()) {
                        $data['errors'][$translator->trans('failed operation')] [] = $document->getId();
                        continue;
                    }
                    if ($errorMessage) {
                        $data['errors'][$errorMessage] [] = $document->getId();
                        continue;
                    }
                    if ($document->getDeleted())
                        continue;
                    try {

                            $document->delete($dm, $this->getUser(), $this->container, $request->get('deleteOption'));

                            $dm->flush();

                            $successIds [] = $document->getId();

                    } catch (\Exception $e) {
                        $data['errors'][$translator->trans('failed operation')] [] = $document->getId();
                    }
                }
                $userPacked = array();
                $usersIds = array();
                if (count($successIds) > 0) {
                    $this->postDelete($successIds);
                }
                break;
            }

            $data['count'] =   $dm->createQueryBuilder($this->getObjectShortName())
                ->field('deleted')->equals(FALSE)
                ->field('product')->equals($productId)
                ->getQuery()
                ->count();

        $data['message'] = str_replace('%success-count%', count($successIds), $message);
        return new JsonResponse($data);
    }
}
