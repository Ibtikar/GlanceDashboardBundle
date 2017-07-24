<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Document\Media;
use Symfony\Component\HttpFoundation\File\File;
use Ibtikar\GlanceDashboardBundle\Form\Type\MediaType;
use Symfony\Component\Filesystem\Filesystem;

class MediaController extends BackendController
{

    protected $translationDomain = 'media';
    protected $calledClassName = 'Media';

    /**
     * @return string
     */
    protected function getObjectShortName()
    {
        return 'IbtikarGlanceDashboardBundle:' . $this->calledClassName;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param Request $request
     * @param type $type
     * @param type $documentId
     * @param type $collectionType
     * @return JsonResponse
     */
    public function uploadAction(Request $request, $type, $documentId = NULL, $collectionType = 'Product')
    {
        if (!$this->getUser()) {

            return $this->getLoginResponse();
        }
        $imageType = $request->get('imageType');
        $fieldUpdate='';

        $dm = $this->get('doctrine_mongodb')->getManager();
        $media = new Media();
        $media->setType($type);
        $media->setCollectionType($collectionType);
        $media->setOrder(99);
        if ($documentId && $documentId != 'null') {
            if ($collectionType === 'SubProduct' || $collectionType === 'Activity') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
                $response = $this->getInvalidResponseForSubProduct($documentId, $imageType,'upload');
                if ($response) {
                    return $response;
                }
                $fieldUpdate='Subproduct';
                if($collectionType === 'Activity'){
                   $fieldUpdate='Activity';
                }
            }elseif ($collectionType === 'Product') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
                $response = $this->getInvalidResponseForProduct($documentId, $imageType,'upload');
                if ($response) {
                    return $response;
                }
                $fieldUpdate='Product';
            }elseif ($collectionType === 'Recipe') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
                $response = $this->getInvalidResponseForRecipe($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                if ($response) {
                    return $response;
                }
                $fieldUpdate='Recipe';
            } elseif ($collectionType === 'Blog') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
                $response = $this->getInvalidResponseForRecipe($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                if ($response) {
                    return $response;
                }
                $fieldUpdate='Recipe';
            } elseif ($collectionType === 'Competition') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
                $response = $this->getInvalidResponseForCompetition($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                if ($response) {
                    return $response;
                }
                $fieldUpdate='Competition';
            } elseif ($collectionType === 'HomeBanner') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }

                $fieldUpdate = 'Banner';
            } else {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }

                $fieldUpdate = 'Banner';
            }
        } else {
            if ($imageType && $imageType!='undefined') {
                switch ($imageType) {
                    case 'profilePhoto':
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                            'type' => $type,
                            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                            'product' => null,
                            'subproduct' => null,
                            'activity' => null,
                            'recipe' => null,
                            'contactMessage' => null,
                            'magazine' => null,
                            'competition' => null,
                            'collectionType' => $collectionType,
                            'ProfilePhoto' => TRUE
                        ));
                        $media->setProfilePhoto(TRUE);
                        if ($document) {
                            return new JsonResponse(array('status' => 'reload'));
                        }
                        break;
                    case 'coverPhoto':
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                            'type' => $type,
                            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                            'product' => null,
                            'contactMessage' => null,
                            'subproduct' => null,
                            'recipe' => null,
                            'magazine' => null,
                            'competition' => null,
                            'collectionType' => $collectionType,
                            'coverPhoto' => TRUE
                        ));
                        $media->setCoverPhoto(TRUE);
                        break;
                    case 'bannerPhoto':
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                            'type' => $type,
                            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                            'product' => null,
                            'contactMessage' => null,
                            'subproduct' => null,
                            'recipe' => null,
                            'magazine' => null,
                            'banner' => null,
                            'competition' => null,
                            'collectionType' => $collectionType,
                            'bannerPhoto' => TRUE
                        ));
                        $media->setBannerPhoto(TRUE);
                        if ($document) {
                            return new JsonResponse(array('status' => 'reload'));
                        }
                        break;
                    case 'activityPhoto':
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                            'type' => $type,
                            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                            'product' => null,
                            'contactMessage' => null,
                            'subproduct' => null,
                            'recipe' => null,
                            'magazine' => null,
                            'banner' => null,
                            'competition' => null,
                            'collectionType' => $collectionType,
                            'activityPhoto' => TRUE
                        ));
                        $media->setActivityPhoto(TRUE);
                        break;
                    case 'naturalPhoto':
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                            'type' => $type,
                            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                            'product' => null,
                            'contactMessage' => null,
                            'subproduct' => null,
                            'recipe' => null,
                            'magazine' => null,
                            'banner' => null,
                            'competition' => null,
                            'collectionType' => $collectionType,
                            'naturalPhoto' => TRUE
                        ));
                        $media->setNaturalPhoto(TRUE);
                        break;
                }

            }


        }



        $media->setCreatedBy($this->getUser());
        if ($documentId && $documentId != 'null' && $fieldUpdate) {
            $functionName = "set$fieldUpdate";
            $media->$functionName($document);
        }
        $extension = '';
        if ($request->get('fileName')) {
            $fileNameArray = explode('.', $request->get('fileName'));
            if (count($fileNameArray) > 0) {
                if (in_array($fileNameArray[count($fileNameArray) - 1], array('gif', 'png', 'jpeg', 'jpg'))) {
                    $extension = $fileNameArray[count($fileNameArray) - 1];
                }
            }
        }


        $validationGroup = array($collectionType);
        $form = $this->createForm(MediaType::class, $media, array(
            'translation_domain' => $this->translationDomain,
            'validation_groups' => $validationGroup,
            'csrf_protection' => false,
            'extension' => $extension
        ));
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($imageType) {
                    switch ($imageType) {
                        case 'profilePhoto':
                            $media->setProfilePhoto(TRUE);
                            if ($documentId && $documentId != 'null') {
                                $functionName = "get$fieldUpdate";
                                $media->$functionName()->setProfilePhoto($media);
                            }

                            break;
                        case 'bannerPhoto':
                            $media->setBannerPhoto(TRUE);
                            if ($documentId && $documentId != 'null') {
                                $functionName = "get$fieldUpdate";
                                $media->$functionName()->setBannerPhoto($media);
                            }

                            break;
                        case 'coverPhoto':
                            $media->setCoverPhoto(TRUE);
                            if ($documentId && $documentId != 'null') {
                                $functionName = "get$fieldUpdate";
                                $media->$functionName()->setCoverPhoto($media);
                        }
                        break;
                        case 'activityPhoto':
                            $media->setActivityPhoto(TRUE);
                            break;
                        case 'naturalPhoto':
                            $media->setNaturalPhoto(TRUE);
                            break;

                    }
                }
                $tempPath = $media->getTempPath();
                $media->setTempPath('');
                $dm->persist($media);
                if ($request->get('fileName')) {
                    $media->setName($request->get('fileName'));
                }

                $dm->flush();



                return new JsonResponse(array('status' => 'success', 'media' => $this->prepareMedia($media,$collectionType,  TRUE), 'message' => $this->trans('upload successfuly')));
            } else {

                $tempPath = $media->getTempPath();
            }
        }
        if ($tempPath) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($media->getTempPath());
        }
        $error = $this->trans('failed operation');
        foreach ($form->getErrors() as $errorObject) {
            $error = $errorObject->getMessage();
            break;
        }
        return new JsonResponse(array('error' => $error), 400);
    }

    private function prepareMedia($media,$collectionType,$flushData=FALSE)
    {
        $getCollection = $collectionType == 'Blog' ? 'Recipe' : $collectionType;
        $getCollection = 'get'.$getCollection;
        if($flushData){
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->flush();
        }
        return array(
            'imageUrl' => $media->getWebPath(),
            'path' => $media->getPath(),
            'id' => $media->getId(),
            'type' => $media->getType(),
            'name' => $media->getName(),
            'coverPhoto' => $media->getCoverPhoto(),
            'activityPhoto' => $media->getActivityPhoto(),
            'changeCoverUrl' => method_exists($media,$getCollection) && $media->$getCollection() ? $this->generateUrl('ibtikar_glance_dashboard_media_change_defaultcover', array('imageId' => $media->getId(), 'documentId' => $media->$getCollection()->getId(), 'collectionType'=>$collectionType)) : '',
            'captionAr' => $media->getCaptionAr()?$media->getCaptionAr():'',
            'caption' => $media->getCaptionAr()?$media->getCaptionAr():'',
            'captionEn' => $media->getCaptionEn()?$media->getCaptionEn():'',
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_delete', array('id' => $media->getId(),'collectionType'=>$collectionType)),
            'cropUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_crop', array('id' => $media->getId(),'collectionType'=>$collectionType)),
            'pop' => str_replace('%title%', $this->trans('image', array(), $this->translationDomain), $this->get('app.twig.popover_factory_extension')->popoverFactory(array("question" => "You are about to delete %title%,Are you sure?")))
        );
    }

    public function cropAction(Request $request, $id,$collectionType)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }

        $fileMedia = $request->get('media');
        if (isset($fileMedia['file']) && $fileMedia['file']) {
            $fileData = explode('base64,', $fileMedia['file']);
            $imageString = base64_decode($fileData[1]);
            $fileSystem = new Filesystem();
            if ($imageString) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $media = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->find($id);
                if (!$media) {
                    return new JsonResponse(array('status', 'reload'));
                }
                if ($media->getProduct()) {
                    $reponse = $this->getInvalidResponseForProduct($media->getProduct()->getId(), '', 'crop');
                    if ($reponse) {
                        return $reponse;
                    }
                }

                if ($media->getSubproduct()) {
                    $reponse = $this->getInvalidResponseForSubProduct($media->getSubproduct()->getId(), '', 'crop');
                    if ($reponse) {
                        return $reponse;
                    }
                }

                if ($media->getMagazine()) {
                    $securityContext = $this->get('security.authorization_checker');
                    if (!$securityContext->isGranted('ROLE_MAGAZINE_EDIT') && !$securityContext->isGranted('ROLE_ADMIN')) {
                        return $this->getAccessDeniedResponse();
                    }
                }

                $imageRandomName = uniqid();
                $uploadDirectory = $media->getUploadRootDir() . '/temp/';
                $fileSystem->mkdir($uploadDirectory, 0755);
                $uploadPath = $uploadDirectory . $imageRandomName;

                if (@file_put_contents($uploadPath, $imageString)) {
                    $file = new \Symfony\Component\HttpFoundation\File\File($uploadPath, false);
                    $imageExtension = $file->guessExtension();
                    $uploadPath = "$uploadDirectory$imageRandomName.$imageExtension";
                    $fileSystem->rename($uploadDirectory . $imageRandomName, $uploadPath);
                    $imageRandomName = "$imageRandomName.$imageExtension";
                    $tempUrlPath = $uploadPath;

                    $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile($uploadPath, $imageRandomName, null, null, 0, true);
                    $media->setFile($uploadedFile);
                    $validationGroup = array($collectionType);
                    $validator = $this->get('validator');
                    $errors = $validator->validate($media,null, $validationGroup);
                    if(count($errors) > 0){
                    if ($tempUrlPath) {
                        $fileSystem = new Filesystem();
                        $fileSystem->remove($tempUrlPath);
                    }
                    return new JsonResponse(array('status' => 'error',  'message' => $errors->first()->getMessage()));

                    }

                    $dm->flush();
                    if ($tempUrlPath) {
                        $fileSystem = new Filesystem();
                        $fileSystem->remove($tempUrlPath);
                    }
                    return new JsonResponse(array('status' => 'success', 'media' => $this->prepareMedia($media,$collectionType,TRUE), 'message' => $this->trans('upload successfuly')));
                }
            }
        }
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function deleteFileAction(Request $request, $id,$collectionType)
    {

        if (!$this->getUser() && $collectionType != "Competition") {
            return $this->getLoginResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        /* @var $document Media */
        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document) {
            return $this->getNotificationResponse(null, array('deleted' => true), 'error');
        }


        if ($collectionType === 'Product' && $document->getProduct()) {
            $reponse = $this->getInvalidResponseForProduct($document->getProduct()->getId(), '', 'delete');
            if ($reponse) {
                return $reponse;
            }
            if ($document->getProduct()) {
                if ($document->getProfilePhoto()) {
                    $document->getProduct()->setProfilePhoto(NULL);
                }
                if ($document->getBannerPhoto()) {
                    $document->getProduct()->setBannerPhoto(NULL);
                }
            }
        }
        if ($collectionType === 'SubProduct' && $document->getSubproduct()) {
            $reponse = $this->getInvalidResponseForSubProduct($document->getSubproduct()->getId(), '', 'delete');
            if ($reponse) {
                return $reponse;
            }
            if ($document->getSubproduct()) {

                if ($document->getProfilePhoto()) {
                    $document->getSubproduct()->setProfilePhoto(NULL);
                }
            }
        }

        if (strpos(strtolower($collectionType), 'bannar') !==FALSE && $document->getBanner() || strpos(strtolower($collectionType), 'banner') !==FALSE && $document->getBanner()) {

            if ($document->getBanner()) {

                if ($document->getBanner()) {
                    $document->getBanner()->setBannerPhoto(NULL);
                }
            }
        }

        if (in_array($collectionType, ['Recipe', 'Blog']) && $document->getRecipe()) {
//            $response = $this->getInvalidResponseForRecipe($document->getRecipe()->getId(), $request->get('room'));
//
//            if ($response) {
//                return $response;
//            }
            if ($document->getCoverPhoto()) {
                return $this->getNotificationResponse($this->trans('cant delete cover photo'), array(), 'error');
            }
        }
        $dm->remove($document);
        try {
            $dm->flush();
        } catch (\Exception $e) {
            return $this->getNotificationResponse(null, array(), 'error');
        }

        return $this->getNotificationResponse($this->trans('done sucessfully'));
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param string $type
     */
    public function userFilesAction($type, $documentId = NULL, $collectionType = 'Product')
    {

        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }

        $documents = array();
        if ($documentId && $documentId != 'null') {
            if ($collectionType === 'Product') {
                $reponse = $this->getInvalidResponseForProduct(new \MongoId($documentId), '', 'list');
                if ($reponse) {
                    return $reponse;
                }
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type == "all" ? array('$in' => array('image', 'video')) : $type,
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'product' => new \MongoId($documentId),
                    'subproduct' => null,
                    'collectionType' => $collectionType
                ), array('order' => 'ASC'));
            } elseif ($collectionType === 'SubProduct') {
                $reponse = $this->getInvalidResponseForSubProduct(new \MongoId($documentId), '', 'list');
                if ($reponse) {
                    return $reponse;
                }
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type,
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'subproduct' => new \MongoId($documentId),
                    'product' => null,
                    'collectionType' => $collectionType
                ), array('order' => 'ASC'));
            }elseif ($collectionType === 'Activity') {
                $reponse = $this->getInvalidResponseForSubProduct(new \MongoId($documentId), '', 'list');
                if ($reponse) {
                    return $reponse;
                }
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type,
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'activity' => new \MongoId($documentId),
                    'product' => null,
                    'collectionType' => $collectionType
                ), array('order' => 'ASC'));
            }elseif ($collectionType === 'Recipe') {
                $reponse = $this->getInvalidResponseForRecipe($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                if ($reponse) {
                    return $reponse;
                }
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type == "all" ? array('$in' => array('image', 'video')) : $type,
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'recipe' => new \MongoId($documentId),
                    'subproduct' => null,
                    'product' => null,
                    'collectionType' => $collectionType
                ),array('order' => 'ASC'));
            } elseif ($collectionType === 'Magazine') {
                $securityContext = $this->get('security.authorization_checker');
                if (!$securityContext->isGranted('ROLE_MAGAZINE_EDIT') && !$securityContext->isGranted('ROLE_ADMIN')) {
                    return $this->getAccessDeniedResponse();
                }
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => 'image',
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'magazine' => new \MongoId($documentId),
                    'subproduct' => null,
                    'product' => null,
                    'recipe' => null,
                    'collectionType' => $collectionType
                ));
            } elseif ($collectionType === 'Blog') {
                $reponse = $this->getInvalidResponseForRecipe($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                if ($reponse) {
                    return $reponse;
                }
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type == "all" ? array('$in' => array('image', 'video')) : $type,
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'recipe' => new \MongoId($documentId),
                    'subproduct' => null,
                    'product' => null,
                    'collectionType' => $collectionType
                ),array('order' => 'ASC'));
            } elseif ($collectionType === 'HomeBanner') {

                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => 'image',
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'banner' => new \MongoId($documentId),
                    'collectionType' => $collectionType
                    ), array('order' => 'ASC'));
            }
            else {

                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => 'image',
//                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'banner' => new \MongoId($documentId),
                    'collectionType' => $collectionType
                    ), array('order' => 'ASC'));
            }
        } else {
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type == "all"?array('$in'=>array('image','video')):$type,
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => null,
                'recipe' => null,
                'blog' => null,
                'contactMessage' => null,
                'magazine' => null,
                'activity' => null,
                'subproduct' => null,
                'activity' => null,
                'collectionType' => $collectionType
            ));
        }

        $files = array();
        $coverPhoto = '';
        $profilePhoto = '';
        $bannerPhoto = '';
        $naturalPhoto = '';

        /* @var $document Media */
        foreach ($documents as $document) {
//            if ($document->getCoverPhoto() && !in_array($collectionType, ['Recipe', 'Blog','Product'])) {
//                $coverPhoto = $this->prepareMedia($document,$collectionType);
//                continue;
//            }
            if ($document->getProfilePhoto()) {
                $profilePhoto = $this->prepareMedia($document,$collectionType);
                continue;
            }
            if ($document->getNaturalPhoto()) {
                $naturalPhoto = $this->prepareMedia($document,$collectionType);
                continue;
            }
            if ($document->getBannerPhoto()) {
                $bannerPhoto = $this->prepareMedia($document,$collectionType);
                continue;
            }
            if($document->getType()=='video'){
                $files [] = $this->getVideoArray($document, $documentId, $collectionType);

            }else{
               $files [] = $this->getMediaDataArray($document, $documentId, $collectionType);

            }
        }

        return new JsonResponse(array('images' => $files, 'coverPhoto' => $coverPhoto, 'profilePhoto' => $profilePhoto,'bannerPhoto'=>$bannerPhoto,'naturalPhoto'=>$naturalPhoto));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param Media $media
     * @param type $documentId
     * @param type $collectionType
     * @return type
     */
    private function getMediaDataArray(Media $media, $documentId = null, $collectionType = null)
    {
        $getCollection = $collectionType == 'Blog' ? 'Recipe' : $collectionType;
        if($documentId) {
            $getCollection = 'get'. $getCollection;
        }
        $data = array(
            'imageUrl' => $media->getWebPath(),
            'id' => $media->getId(),
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_delete', array('id' => $media->getId(),'collectionType'=>$collectionType)),
            'cropUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_crop', array('id' => $media->getId(),'collectionType'=>$collectionType)),
            'pop' => str_replace('%title%', $this->trans('image', array(), $this->translationDomain), $this->get('app.twig.popover_factory_extension')->popoverFactory([])),
            'captionAr' => $media->getCaptionAr()?$media->getCaptionAr():"",
            'captionEn' => $media->getCaptionEn()?$media->getCaptionEn():"",
            'path' => $media->getPath(),
            'name' => $media->getName(),
            'type' => $media->getType(),
            'cover'=>$media->getCoverPhoto()?'checked':'',
            'activityPhoto'=>$media->getActivityPhoto(),
            'coverPhoto'=>$media->getCoverPhoto(),
            'changeCoverUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_change_defaultcover', array('imageId' => $media->getId(), 'documentId' => $documentId, 'collectionType'=>$collectionType)),
        );
        return $data;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param type $id
     * @param type $type
     * @return type
     * @throws type
     */
    public function getInvalidResponseForProduct($documentId, $imageType = '',$type='upload')
    {
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_PRODUCT_EDIT') && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }
        if ($imageType) {
            switch ($imageType) {
                case 'profilePhoto':
                    $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                        'type' => 'image',
                        'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                        'product' => new \MongoId($documentId),
                        'subproduct' => null,
                        'collectionType' => 'Product',
                        'ProfilePhoto' => TRUE
                    ));
                    break;
                case 'bannerPhoto':
                    $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                        'type' => 'image',
                        'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                        'product' => null,
                        'product' => new \MongoId($documentId),
                        'collectionType' => 'Product',
                        'bannerPhoto' => TRUE
                    ));
                    break;
            }
            if (isset($document) && $document && $type=='upload') {
                return new JsonResponse(array('status' => 'reload'));
            }
        }
    }

    public function getInvalidResponseForSubProduct($documentId, $imageType = '',$type='upload')
    {
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_SUBPRODUCT_EDIT') && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }
        if ($imageType) {
            switch ($imageType) {
                case 'profilePhoto':
                    $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                        'type' => 'image',
                        'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                        'subproduct' => new \MongoId($documentId),
                        'product' => null,
                        'collectionType' => 'SubProduct',
                        'ProfilePhoto' => TRUE
                    ));
                    break;

            }
            if ($document && $type=='upload') {
                return new JsonResponse(array('status' => 'reload'));
            }
        }
    }

    /**
     * @author ahmad Gamal<a.gamal@ibtikar.net.sa>
     */
    public function upload_imageUrlAction(Request $request)
    {

        $imageUrl = $request->get('imageUrl');
        if (!$imageUrl || @filter_var($imageUrl, FILTER_VALIDATE_URL) === FALSE) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        $headers = get_headers($imageUrl, 1);

        if (isset($headers["Content-Length"])) {
            $content_length = $headers["Content-Length"];
        } else if ($remote_filesize = $this->remote_filesize($imageUrl)) {
            $content_length = $remote_filesize;
        } else {
            $responseContent = "error";
            return new Response($responseContent);
        }

        if (is_array($content_length)) {
            foreach ($content_length as $content_len) {
                if ($content_len && $content_len > 4194304) {
                    $responseContent = "errorImageFileSize";
                    return new Response($responseContent);
                }
            }
        } else {
            if ($content_length && $content_length > 4194304) {
                $responseContent = "errorImageFileSize";
                return new Response($responseContent);
            }
        }

        $imagesize = @getimagesize($imageUrl);

        if (!$imagesize) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        if ($imagesize[0] < 1170 || $imagesize[1] < 600) {
            $responseContent = "errorImageSize";
            return new Response($responseContent);
        }

        // validate image extension
        $imageExt = explode('/', $imagesize['mime']);
        $avilableExt = array('png', 'jpg', 'jpeg', 'gif');
        if (!in_array($imageExt[1], $avilableExt)) {
            $responseContent = "errorImageExtension";
            return new Response($responseContent);
        }


        $responseContent = "success";
        return new Response($responseContent);
    }

    private function remote_filesize($url)
    {
        static $regex = '/^Content-Length: *+\K\d++$/im';
        if (!$fp = @fopen($url, 'rb')) {
            return false;
        }
        if (isset($http_response_header) && preg_match($regex, implode("\n", $http_response_header), $matches)) {
            return (int) $matches[0];
        }
        return strlen(stream_get_contents($fp));
    }

    /**
     * @author ahmad Gamal<a.gamal@ibtikar.net.sa>
     */
    public function validateYoutubeVideoUrlAction(Request $request)
    {
        $videoUrl = $request->get('videoUrl');
        if (!$videoUrl || @filter_var($videoUrl, FILTER_VALIDATE_URL) === FALSE) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        $rx = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        $has_match = preg_match($rx, $videoUrl, $matches);

        if (empty($matches)) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        $videoUrlId = $matches[1];

        return new Response($videoUrlId);
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function uploadImageFromGoogleAction(Request $request, $documentId = null, $collectionType = 'Material')
    {

        // die(var_dump($request->request->all()));
        $data = array(
            'status' => 'success',
            'message' => $this->trans('upload successfuly'),
            'files' => &$files,
            'success' => &$successIds,
            'errors' => array()
        );
        if ($request->getMethod() === 'POST') {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $images = $request->get('images');
            $documentDir = __DIR__ . '/../../../../web/uploads/' . strtolower($collectionType) . '-file/' . $this->getUser()->getId() . '/';
            if (!empty($images)) {
                if (!file_exists($documentDir)) {
                    mkdir($documentDir, 0755, true);
                }
            }
            foreach ($images as $image) {
                $url = $image['url'];
//                $urlArray = explode('/', $url);
//                $name = $urlArray[count($urlArray) - 1];
                $imagesize = @getimagesize($url);
                if (!$imagesize) {
                    $data['errors']['فشل فى رفع الصورة'][] = $url;
                    continue;
                }

                $imageExt = explode('/', $imagesize['mime']);
                // $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                $extension = $imageExt[1];
//                $urlArray = explode('/', $url);
//                $name = $urlArray[count($urlArray) - 1];
                $media = new Media();
                $media->setCreatedBy($this->getUser());
                $media->setCollectionType($collectionType);
                if ($documentId && $documentId != 'null') {
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Recipe' )->find($documentId);
                    $collectionSetter = "setRecipe" ;
                    $media->$collectionSetter($document);
                    $lastPic = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($this->getObjectShortName())
                            ->field('recipe')->equals($documentId)
                            ->field('type')->equals('image')
                            ->sort('order', 'DESC')
                            ->getQuery()->getSingleResult();
                    $order = 0;
                    if ($lastPic) {
                        $order = $lastPic->getOrder() + 1;
                    }
                    $media->setOrder($order);
                }
                $name = $media->getImagePath($extension);
                $filePath = $documentDir . $name;
                @file_put_contents($filePath, file_get_contents($image['url']));
                //  $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $file = new File($filePath);
                $media->setFile($file);

                $urlArray = explode('/', $url);
                $imageNameFromGoogle = $urlArray[count($urlArray) - 1];
                $imageName = explode('?', $imageNameFromGoogle);
                $media->setName($imageName[0]);
                $media->setType('image');
                $media->setPath($media->getImagePath($extension));
                $validator = $this->get('validator');
                $validationGroup = array(strtolower($collectionType));
                $errors = $validator->validate($media, NULL, $validationGroup);

                if (count($errors) > 0) {
                    $data['status']='error';
                    foreach ($errors as $error) {
                        $data['message'] = $error->getMessage();
                    }
                } else {
                    $dm->persist($media);
                    $dm->flush();

                    $successIds [] = $url;
                    $data['media'] = $this->prepareMedia($media, $collectionType);
                }
                @unlink($filePath);
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param type $url
     * @return resource
     */
    private function getCurlInstance($url)
    {
        $ch = curl_init();
        // cloudflare will block the request if it does not contain the user agent string
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        return $ch;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param string $url
     * @return string
     */
    private function followRedirectAndGetFinalURL($url)
    {
        $ch = $this->getCurlInstance($url);
        curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $finalUrl;
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     *
     * @param string $url
     * @return image
     */
    private function getImg($url)
    {
        $ch = $this->getCurlInstance($url);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }




    public function uploadYoutubeVideoAction(Request $request,$type='video',$documentId = null, $collectionType = 'Material')
    {

        $data = array(
            'status' => 'success',

        );
        if ($request->getMethod() === 'POST') {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $videos = $request->get('videos');

            foreach ($videos as $video) {
                $videoObj = new Media();
                if($collectionType == "Competition" || $collectionType == "Activity" ){
                    $objId = null;
                    if($documentId && $documentId != 'null'){
                        $objId = $documentId;
                    }

                    $findBy = array(
                        'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                        strtolower($collectionType) => $objId,
                        'collectionType' => $collectionType,
                    );

                    if($collectionType !== "Product"){
                        $findBy['type'] = 'video';
                    }else{
                        $findBy['coverPhoto'] = true;
                    }

                    $prevVideo = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findOneBy($findBy);
                    if ($collectionType == "Activity") {
                        $obj = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->find($documentId);
                    } else {
                        $obj = $dm->getRepository('IbtikarGlanceDashboardBundle:' . $collectionType)->find($documentId);
                    }

                    if($prevVideo){
                        $videoObj = $prevVideo;
                    }
//                    elseif ($documentId && $documentId != 'null') {
//                        $method = "set" . $collectionType;
//                        $videoObj->$method($obj);
//                        if ($collectionType == "Competition") {
//                            if (method_exists($obj, 'setCoverPhoto')) {
//                                $obj->setCoverPhoto($videoObj);
//                            }
//                        }
//                    }
                }
                $video = explode('#', $video);
                $vid = $video[0];
                $videoObj->setVid($vid);
                if ($request->get('collectionType')) {
                    $videoObj->setCollectionType($request->get('collectionType'));
                }
                if ($documentId && $documentId != 'null') {
                    if ($request->get('collectionType') == 'Recipe' || $request->get('collectionType') == 'Blog') {
                        $recipe = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($documentId);
                        if (!$recipe) {
                            throw $this->createNotFoundException($this->trans('Wrong id'));
                        }
                        $reponse = $this->getInvalidResponseForRecipe($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                        if ($reponse) {
                            return $reponse;
                        }
                        $lastVideo = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($this->getObjectShortName())
                                        ->field('recipe')->equals($documentId)
                                        ->sort('order', 'DESC')
                                        ->getQuery()->getSingleResult();
                        $order = 0;
                        if ($lastVideo) {
                            $order = $lastVideo->getOrder() + 1;
                        }
                        $videoObj->setOrder($order);
                        $videoObj->setRecipe($recipe);
                    } elseif($request->get('collectionType') == 'Competition') {

                        $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($documentId);
                        if (!$competition) {
                            throw $this->createNotFoundException($this->trans('Wrong id'));
                        }
                        $reponse = $this->getInvalidResponseForCompetition($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                        if ($reponse) {
                            return $reponse;
                        }
                        $videoObj->setCompetition($competition);
                    } elseif($request->get('collectionType') == 'Product') {

                        $subproduct = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($documentId);
                        if (!$subproduct) {
                            throw $this->createNotFoundException($this->trans('Wrong id'));
                        }
                    }
                     elseif($request->get('collectionType') == 'Activity') {

                        $subproduct = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->find($documentId);
                        if (!$subproduct) {
                            throw $this->createNotFoundException($this->trans('Wrong id'));
                        }
                        $videoObj->setActivity($subproduct);
                    }
                    else {
                        $task = $dm->getRepository('IbtikarBackendBundle:Task')->find($documentId);
                        $lastVideo = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($this->getObjectShortName())
                                ->field('task')->equals($documentId)
                                ->sort('order', 'DESC')
                                ->getQuery()->getSingleResult();
                        $order = 0;
                        if ($lastVideo) {
                            $order = $lastVideo->getOrder() + 1;
                        }
                        $videoObj->setOrder($order);
                        $videoObj->setTask($task);
                    }
                }
                $imageType = $request->get('imageType');

                if ($imageType) {
                    if ($imageType && $imageType != 'undefined') {
                        switch ($imageType) {
                            case 'activityPhoto':
                                $videoObj->setActivityPhoto(TRUE);
                                break;
                            case 'coverPhoto':
                                $videoObj->setCoverPhoto(TRUE);

                                break;
                            case 'profilePhoto':
                                $videoObj->setProfilePhoto(TRUE);
                                break;
                        }
                    }
                }

                $videoObj->setCreatedBy($this->getUser());
                $videoObj->setType($type);
                $dm->persist($videoObj);
                $dm->flush();
                $data['message']=  $this->trans('upload successfuly');
                $data['video'][]=$this->getVideoArray($videoObj, $documentId,$request->get('collectionType'));
            }
        }
        return new JsonResponse($data);
    }


    private function getVideoArray($video, $documentId = null,$collectionType='Recipe') {
        $routeParameters = array('id' => $video->getId(),'collectionType' => $collectionType);
        if($documentId) {
            $routeParameters['materialId'] = $documentId;
        }
        $data = array(
            'id' => $video->getId(),
            'vid' => $video->getVid(),
            'imageUrl'=>'https://i.ytimg.com/vi/' . $video->getVid() . '/hqdefault.jpg',
            'captionAr' => is_null($video->getCaptionAr())?"":$video->getCaptionAr(),
            'captionEn' => is_null($video->getCaptionEn())?"":$video->getCaptionEn(),
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_video_delete', $routeParameters),
            'type' => $video->getType(),
            'cover'=>$video->getCoverPhoto()?'checked':'',
            'activityPhoto'=>$video->getActivityPhoto(),
            'coverPhoto'=>$video->getCoverPhoto(),
            'changeCoverUrl' => $documentId ? $this->generateUrl('ibtikar_glance_dashboard_media_change_defaultcover', array('imageId' => $video->getId(), 'documentId' => $documentId, 'collectionType'=>$collectionType)) : '',
        );
        return $data;
    }

    public function deleteVideoAction(Request $request ,$id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        /* @var $document Media */

        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document) {
            return $this->getNotificationResponse(null, array(), 'error');
        }
        $documentId = $request->get('documentId');
        $collectionType = $request->get('collectionType');

        if ($documentId && $documentId != 'null' && $collectionType == 'Recipe') {
//            $response = $this->getInvalidResponseForRecipe($documentId, $request->get('room'));
//            if ($response) {
//                return $response;
//            }
            if ($document->getCoverPhoto()) {
                return $this->getNotificationResponse($this->trans('cant delete cover photo'), array(), 'error');
            }
        }

//        if ($collectionType == 'Task') {
//            if ($document->getTask() === null && $document->getCreatedBy()->getId() !== $this->getUser()->getId()) {
//                return $this->getNotificationResponse(null, array(), 'error');
//            }
//        } else {
//            if ($document->getMaterial() === null && $document->getCreatedBy()->getId() !== $this->getUser()->getId()) {
//                return $this->getNotificationResponse(null, array(), 'error');
//            }
//        }

        $dm->remove($document);
        try {
            $dm->flush();
        } catch (\Exception $e) {
            return $$this->getNotificationResponse(null, array(), 'error');
        }
        return $this->getNotificationResponse($this->trans('done sucessfully'));
    }


    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     *
     */
    public function imageProxyAction(Request $request)
    {
        $ch = $this->getCurlInstance($request->get('url'));
        $return = curl_exec($ch);
        curl_close($ch);
        return new Response(
            $return,
            200,
            array(
                'Content-Type' => 'image/jpeg'
            )
        );
    }

    public function getInvalidResponseForRecipe($documentId, $room) {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $roomArray=  explode('?', $room);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $material = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($documentId);
        if (!$material) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $securityContext = $this->get('security.authorization_checker');
        if (!$room || (!$securityContext->isGranted('ROLE_' . strtoupper($roomArray[0]) . '_EDIT') && !$securityContext->isGranted('ROLE_ADMIN'))) {

            return $this->getAccessDeniedResponse();
        }

    }

    public function getInvalidResponseForCompetition($documentId, $room) {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $roomArray=  explode('?', $room);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $material = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($documentId);
        if (!$material) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $securityContext = $this->get('security.authorization_checker');
        if (!$room || (!$securityContext->isGranted('ROLE_' . strtoupper($roomArray[0]) . '_EDIT') && !$securityContext->isGranted('ROLE_ADMIN'))) {

            return $this->getAccessDeniedResponse();
        }

    }

    /**
     * @author Ahmad Gamal <a.gamal@ibtikar.net.sa>
     * @param Request $request
     */
    public function changeDefaultCoverAction(Request $request, $imageId, $documentId, $collectionType) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($documentId);
        if (!$document) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $media = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->find($imageId);
        if (!$media) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $documentImages = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('recipe' => $documentId, 'coverPhoto' => true));

        foreach ($documentImages as $key => $documentImage) {
            $documentImage->setCoverPhoto(FALSE);
        }

        $media->setCoverPhoto(TRUE);

        $document->setCoverPhoto($media);
        $dm->flush();

        return new JsonResponse(array('status' => 'success'));
    }
}
