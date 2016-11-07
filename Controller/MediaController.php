<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Document\Media;
use Symfony\Component\HttpFoundation\File\File;
use Ibtikar\GlanceDashboardBundle\Form\Type\MediaType;

class MediaController extends BackendController {

    protected $translationDomain = 'media';
    protected $calledClassName = 'Media';

    /**
     * @return string
     */
    protected function getObjectShortName() {
        return 'IbtikarGlanceDashboardBundle:' . $this->calledClassName;
    }



    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param string $type
     */
    public function userFilesAction($type, $documentId=NULL, $collectionType= 'Material') {
        if ($documentId && $documentId != 'null') {
            if ($collectionType === 'Material') {
                $response = $this->getInvalidResponseForMaterial($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Comics') {
                $response = $this->getInvalidResponseForComic($documentId);
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Event') {
                $response = $this->getInvalidResponseForEvent($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } else {
                if ($collectionType != 'ReplyMessage' && $collectionType != 'City' && $collectionType != 'Place' && $collectionType != 'Task') {
                    return $this->getAccessDeniedResponse();
                }
            }
            if ($collectionType == 'ReplyMessage') {
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type,
//                    'createdBy' => $this->getUser()->getId(),
                    'material' => null,
                    'comics' => null,
                    'event' => null,
                    'place' => null,
                    'contactMessage' => new \MongoId($documentId),
                    'replyMessage' => null,
                    'collectionType' => 'ReplyMessage'
                ));
                $collectionType = 'ContactMessage';
                $documentId=null;
            }  else if ($collectionType == 'City') {
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type,
//                    'createdBy' => $this->getUser()->getId(),
                    'material' => null,
                    'comics' => null,
                    'event' => null,
                    'place' => null,
                    'contactMessage' => null,
                    'replyMessage' => null,
                    'city'=>new \MongoId($documentId),
                    'collectionType' => 'City'
                ));
                $documentId=null;
            } else if ($collectionType == 'Place') {
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type,
//                    'createdBy' => $this->getUser()->getId(),
                    'material' => null,
                    'comics' => null,
                    'event' => null,
                    'city' => null,
                    'contactMessage' => null,
                    'replyMessage' => null,
                    'place'=>new \MongoId($documentId),
                    'collectionType' => 'Place'
                ));
                $documentId=null;
            }else if ($collectionType == 'Task') {
                $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                    'type' => $type,
//                    'createdBy' => $this->getUser()->getId(),
                    'material' => null,
                    'comics' => null,
                    'event' => null,
                    'city' => null,
                    'contactMessage' => null,
                    'replyMessage' => null,
                    'task'=>new \MongoId($documentId),
                    'collectionType' => 'Task'
                ));
                $documentId=null;
            }
            else {
                $documents = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($this->getObjectShortName())
                                ->field(strtolower($collectionType))->equals($documentId)
                                ->field('type')->equals($type)
                                ->sort('order', 'ASC')
                                ->getQuery()->execute();
            }
        } else {
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type,
                'createdBy' => $this->getUser()->getId(),
                'material' => null,
                'comics' => null,
                'event' => null,
                'place' => null,
                'contactMessage' => null,
                'task' => null,
                'replyMessage' => null,
                'city'=>null,
                'collectionType' => $collectionType
            ));
        }

        $files = array();
        /* @var $document Media */
        foreach ($documents as $document) {
            $files [] = $this->getMediaDataArray($document, $documentId, $collectionType);
        }
        return new JsonResponse($files);
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Media $media
     */
    private function clearImageFilters(Media $media) {
        $this->get('liip_imagine.cache.manager')->remove($media->getWebPath());
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param string $id
     */
    public function deleteFileAction($id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        /* @var $document Media */
        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document) {
            return $this->getNotificationResponse(null, array('deleted' => true), 'error');
        }

        $request = $this->getRequest();
        $documentId = $request->get('documentId');
        if ($documentId && $documentId != 'null') {
            $collectionType = $request->get('collectionType');
            if ($collectionType === 'Material') {
                $response = $this->getInvalidResponseForMaterial($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Comics') {
                $response = $this->getInvalidResponseForComic($documentId);
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Event') {
                $response = $this->getInvalidResponseForEvent($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } else {
                if ($collectionType !== 'ReplyMessage' && $collectionType !== 'ContactMessage' &&  $collectionType !== 'City'  && $collectionType !== 'Place' && $collectionType != 'Task' ) {
                    return $this->getAccessDeniedResponse();
                }
            }
        }

        if($document->getComics() === null && $document->getEvent() === null){
            if ($document->getMaterial() === null && $document->getCreatedBy()->getId() !== $this->getUser()->getId()) {

                return $this->getNotificationResponse(null, array(), 'error');
            }
        }
        if ($document->getMaterial() != null) {
            if($document->getMaterial()->getSlug()) {
                $this->get('cache_operations')->invalidateDocumentTag($document->getMaterial()->getSlug(), 'view');
            }
            if ($document->getCoverPhoto()) {
                if ($document->getMaterial()->getStatus() == 'published' || $document->getMaterial()->getStatus() == 'autopublish' ) {
                    $publishLocation = $document->getMaterial()->getPublishLocations();
                    $exist = FALSE;
                    foreach ($publishLocation as $location) {
                        if (strpos($location->getSection(),'Slideshow') !== FALSE) {
                            $exist = TRUE;
                            break;
                        }
                    }
                    if ($exist) {
                        return $this->getNotificationResponse($this->trans('cant delete image'), array(), 'error');
                    }
                }
                $document->getMaterial()->setDefaultCoverPhoto(NULL);
            }
        } else if($document->getComics()!=null){
            if($document->getComics()->getSlug()) {
                $this->get('cache_operations')->invalidateDocumentTag($document->getComics()->getSlug(), 'view');
            }
           if($document->getCoverPhoto()){
               $document->getComics()->setDefaultCoverPhoto(NULL);
           }
        } else if ($document->getEvent() != null) {
            if ($document->getEvent()->getSlug()) {
                $this->get('cache_operations')->invalidateDocumentTag($document->getEvent()->getSlug(), 'view');
            }
            if ($document->getCoverPhoto()) {
                $document->getEvent()->setDefaultCoverPhoto(NULL);
            }
        } else if ($document->getCity() != null) {

            if ($document->getCoverPhoto()) {
                $document->getCity()->setDefaultCoverPhoto(NULL);
            }
        }
        $this->clearImageFilters($document);
        if ($document->getMaterial() != null) {
            $this->get('facebook_scrape')->update($document->getMaterial());
        }
        else if ($document->getComics() != null) {
            $this->get('facebook_scrape')->update($document->getComics());
        }
        else if ($document->getEvent() != null) {
            $this->get('facebook_scrape')->update($document->getEvent());
        }
        $dm->remove($document);
        $material = $document->getMaterial();
        if($material && $document->getType() === 'image') {
            $material->setImagesCount($material->getImagesCount() - 1);
        }
        try {
            $dm->flush();
        } catch (\Exception $e) {
            return $this->getNotificationResponse(null, array(), 'error');
        }
        return $this->getNotificationResponse($this->trans('Deleted sucessfully.'));
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Media $media
     * @param string|null $documentId
     * @param string|null $collectionType
     * @return array
     */
    private function getMediaDataArray(Media $media, $documentId = null, $collectionType = null) {
        if($media->getCoverPhoto()){
            $check="checked";
        }else{
            $check=" ";
        }
        if($media->getProfilePhoto()){
            $pchecked='checked';
        }else{
            $pchecked='';
        }
        $routeParameters = array('id' => $media->getId());
        if ($documentId) {
            $routeParameters['documentId'] = $documentId;
            if($collectionType === 'Material' || $collectionType === 'Event') {
                $routeParameters['room'] = $this->getRequest()->get('room');
            }
        }
        if ($collectionType) {
            $routeParameters['collectionType'] = $collectionType;
        }
        $data = array(
            'id' => $media->getId(),
            'deleteUrl' => $this->generateUrl('backend_media_delete', $routeParameters),
            'path' => $media->getWebPath(),
            'namePath' => $media->getPath(),
            'name' => $media->getName() ? $media->getName() : '',
            'order' => $media->getOrder(),
            'cover' => $check,
            'copyright' => (bool)$media->getCopyright(),
            'isGif' => @exif_imagetype($media->getAbsolutePath()) == IMAGETYPE_GIF,
            'disabled'=>''
        );
        $caption = '';
        if ($media->getCaption()) {
            $caption = $media->getCaption();
        }
        if ($collectionType == 'City') {
            $data['pchecked'] = $pchecked;
            $imagesize = @getimagesize($media->getWebPath());
            if ($imagesize[0] < 1920) {
                $data['disabled'] = 'disabled';
                $data['cover']='';
            }
        }
        $data['caption'] = $caption;

        if ($media->getType() === 'image') {
            $webPath = $media->getWebPath();
            // Fix the migration news images was never resized because the images was saved with the path containing double dots ".." and the resize images bundle does not allow resizing images with double dots in the path
            if(preg_match('#uploads/material-file/\w{24}/../../#', $webPath)) {
                $webPath = 'uploads' . substr($webPath, 52);
            }
            $data['src'] = $this->followRedirectAndGetFinalURL($this->get('liip_imagine.cache.manager')->getBrowserPath($webPath, 'resize_225_150'));
        }
        return $data;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @param string $type
     */
    public function uploadAction(Request $request, $type, $documentId = NULL, $collectionType = 'Product') {
        $media = new Media();
        $media->setType($type);
        $media->setCollectionType($collectionType);
        if ($documentId && $documentId != 'null') {

        }
        $media->setCreatedBy($this->getUser());


        $validationGroup= array($type);
        $form = $this->createForm(MediaType::class, $media, array(
            'translation_domain' => $this->translationDomain,
            'validation_groups' => $validationGroup
        ));
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($media);
                $dm->flush();
//                if ($type == 'image') {
//                    $this->container->get('image_operations')->autoRotate($media->getAbsolutePath());
//                }
                return $this->getNotificationResponse($this->trans('Uploaded sucessfully.'), $this->getMediaDataArray($media, $documentId, $collectionType));
            }
        }
        $error = $this->trans('failed operation');
        foreach ($form->getErrors() as $errorObject) {
            $error = $errorObject->getMessage();
            break;
        }
        return new JsonResponse(array('error' => $error), 400);
    }

    public function cropAction(Request $request) {
        $documentId = $request->get('documentId');
        if ($documentId && $documentId != 'null') {
            $collectionType = $request->get('collectionType');
            if ($collectionType === 'Material') {
                $response = $this->getInvalidResponseForMaterial($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Comics') {
                $response = $this->getInvalidResponseForComic($documentId);
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Event') {
                $response = $this->getInvalidResponseForEvent($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } else {
                if ($collectionType != 'City' && $collectionType != 'Place' && $collectionType != 'Task') {
                    return $this->getAccessDeniedResponse();
                }
            }
        }

        if ($request->getMethod() === 'POST') {

            $dm = $this->get('doctrine_mongodb')->getManager();

            $cacheManager = $this->container->get('liip_imagine.cache.manager');
            $coords = $request->get('coords');

            $queryStrIndex = strrpos($request->get('path'), "?");

            if ($queryStrIndex) {
                $pathNoQueryStr = substr($request->get('path'), 0, strrpos($request->get('path'), "?"));
            } else {
                $pathNoQueryStr = $request->get('path');
            }
            // Fix the migration news images was never resized because the images was saved with the path containing double dots ".." and the resize images bundle does not allow resizing images with double dots in the path
            if (preg_match('#uploads/material-file/\w{24}/../../#', $pathNoQueryStr)) {
                $pathNoQueryStr = 'uploads' . substr($pathNoQueryStr, 52);
            }

            $srcPath = $cacheManager->getBrowserPath($pathNoQueryStr, 'cropper', array('crop' => array('start' => [$coords['x'], $coords['y']], 'size' => [$coords['w'], $coords['h']])));

            $document = $dm->getRepository($this->getObjectShortName())->find($request->get('id'));

            if (!$document) {
                return $this->getFailedAlertResponse();
            }

            if ($documentId && $documentId != 'null') {
            if ($collectionType === 'Material') {
                $relatedDocument = $dm->getRepository('IbtikarAppBundle:Material')->find($documentId);
            } elseif ($collectionType === 'Comics') {
                $relatedDocument = $dm->getRepository('IbtikarAppBundle:Comics')->find($documentId);
                if($relatedDocument->getStatus() == 'published' && $document->getCoverPhoto()){
                    $cachedLocation = array();
                    foreach($relatedDocument->getPublishLocations() as $location){
                        if(strpos(strtolower($location->getSection()), 'gallery') && !strpos(strtolower($location->getSection()), 'home')){
                            $cachedLocation[] = $this->generateUrl('gallery_by_section_json', array('section' => $location->getSection()));
                        }
                    }
                    $this->get('cache_operations')->deleteMulti($cachedLocation);
                }
            } elseif ($collectionType === 'Event') {
                $relatedDocument = $dm->getRepository('IbtikarAppBundle:Event')->find($documentId);
            }
            if($collectionType !='Task' && $relatedDocument->getSlug()) {
                $this->get('cache_operations')->invalidateDocumentTag($relatedDocument->getSlug(), 'view');
            }
            }

            file_put_contents($document->getAbsolutePath(), $this->getImg($srcPath));

            $this->clearImageFilters($document);
            if ($document->getCollectionType()=='City' && $document->getCity() && $document->getCoverPhoto()) {
                $imagesize = @getimagesize($document->getWebPath());
                if ($imagesize[0] < 1920) {
                    $document->getCity()->setDefaultCoverPhoto($document->getPath());
                    $document->setCoverPhoto(FALSE);
                    $dm->flush();
                }
            }


            return new JsonResponse(array('success' => $document->getId(), "image" => $this->getMediaDataArray($document, $documentId, $document->getCollectionType())));
        }
    }

    /**
     * @author ahmad Gamal<a.gamal@ibtikar.net.sa>
    */
    public function upload_imageUrlAction(Request $request) {
        $documentId = $request->get('documentId');
        if ($documentId && $documentId != 'null') {
            $collectionType = $request->get('collectionType');
            if ($collectionType === 'Material') {
                $response = $this->getInvalidResponseForMaterial($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Comics') {
                $response = $this->getInvalidResponseForComic($documentId);
                if ($response) {
                    return $response;
                }
            } elseif ($collectionType === 'Event') {
                $response = $this->getInvalidResponseForEvent($documentId, $this->getRequest()->get('room'));
                if ($response) {
                    return $response;
                }
            } else {
                if ($collectionType != 'City' && $collectionType != 'Place') {
                    return $this->getAccessDeniedResponse();
                }
            }
        }

        $imageUrl = $request->get('imageUrl');
        if(!$imageUrl || @filter_var($imageUrl, FILTER_VALIDATE_URL) === FALSE) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        $headers = get_headers($imageUrl, 1);

        if (isset($headers["Content-Length"])) {
            $content_length = $headers["Content-Length"];
        } else if($remote_filesize = $this->remote_filesize($imageUrl)) {
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

        if(!$imagesize) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        if($imagesize[0] < 200 || $imagesize[1] < 200) {
            $responseContent = "errorImageSize";
            return new Response($responseContent);
        }

        // validate image extension
        $imageExt = explode('/', $imagesize['mime']);
        $avilableExt = array('png', 'jpg', 'jpeg', 'gif');
        if(!in_array($imageExt[1], $avilableExt)) {
            $responseContent = "errorImageExtension";
            return new Response($responseContent);
        }


        $responseContent = "success";
        return new Response($responseContent);
    }

    private function remote_filesize($url) {
        static $regex = '/^Content-Length: *+\K\d++$/im';
        if (!$fp = @fopen($url, 'rb')) {
            return false;
        }
        if ( isset($http_response_header) && preg_match($regex, implode("\n", $http_response_header), $matches) ) {
            return (int)$matches[0];
        }
        return strlen(stream_get_contents($fp));
    }

    /**
     * @author ahmad Gamal<a.gamal@ibtikar.net.sa>
    */
    public function validateYoutubeVideoUrlAction(Request $request) {
        $videoUrl = $request->get('videoUrl');
        if(!$videoUrl || @filter_var($videoUrl, FILTER_VALIDATE_URL) === FALSE) {
            $responseContent = "error";
            return new Response($responseContent);
        }

        $rx = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        $has_match = preg_match($rx, $videoUrl, $matches);

        if(empty($matches)) {
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
    public function uploadImageFromGoogleAction(Request $request, $documentId=null, $collectionType = 'Material') {

        // die(var_dump($request->request->all()));
        $data = array(
            'status' => 'success',
            'message' => '',
            'files'=> &$files,
            'success' => &$successIds,
            'errors' => array()
        );
        if ($request->getMethod() === 'POST') {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $images = $request->get('images');
            $documentDir = __DIR__ . '/../../../../web/uploads/'.  strtolower($collectionType) . '-file/google/' . $this->getUser()->getId() . '/';
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
                    if ($collectionType === 'Material') {
                        $response = $this->getInvalidResponseForMaterial($documentId, $this->getRequest()->get('room'));
                        if ($response) {
                            return $response;
                        }
                    } elseif ($collectionType === 'Comics') {
                        $response = $this->getInvalidResponseForComic($documentId);
                        if ($response) {
                            return $response;
                        }
                    } elseif ($collectionType === 'Event') {
                        $response = $this->getInvalidResponseForEvent($documentId, $this->getRequest()->get('room'));
                        if ($response) {
                            return $response;
                        }
                    } else {
                        if($collectionType != 'City' && $collectionType != 'Place'){
                        return $this->getAccessDeniedResponse();
                        }
                    }
                    if ($collectionType == 'City') {
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarBackendBundle:' . $collectionType)->find($documentId);
                    } elseif ($collectionType == 'Place') {
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarBackendBundle:' . $collectionType)->find($documentId);
                        $documentMedia = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarAppBundle:Media')->findOneBy(array('place' => $documentId));
                        if($documentMedia){
                            $document->setCopyright(true);
                            $this->get('doctrine_mongodb')->getManager()->remove($documentMedia);
                        }
                    } else {
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarAppBundle:' . $collectionType)->find($documentId);
                    }
                    $collectionSetter = "set" . $collectionType;
                    $media->$collectionSetter($document);
                    $lastPic = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($this->getObjectShortName())
                                    ->field(strtolower($collectionType))->equals($documentId)
                                    ->field('type')->equals('image')
                                    ->sort('order', 'DESC')
                                    ->getQuery()->getSingleResult();
                    $order=0;
                    if($lastPic){
                        $order=$lastPic->getOrder() + 1;
                    }
                    $media->setOrder($order);
                    if (method_exists($document, 'getSlug') && $document->getSlug()) {
                        $this->get('cache_operations')->invalidateDocumentTag($document->getSlug(), 'view');
                    }
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
                if ($collectionType == 'Place' && $documentId == 'null') {
                    $errors = $validator->validate($media, array('image', 'oneImage'));
                } else {
                    $errors = $validator->validate($media, array('image'));
                }

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $data['errors'][$error->getMessage()][] = $url;
                    }
                } else {
                    $dm->persist($media);
                    $dm->flush();
                    if ($media->getPlace() != NULL) {
                        $media->getPlace()->setDefaultCoverPhoto($media->getPath());
                        $media->getPlace()->setCopyright(TRUE);
                        $dm->flush();
                    }

                    $successIds [] = $url;
                    $files [] = $this->getMediaDataArray($media, $documentId, $collectionType);
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
    private function getCurlInstance($url) {
        $ch = curl_init();
        $cookiesStringToPass = '';
        foreach ($this->getRequest()->cookies->all() as $name => $value) {
            if ($cookiesStringToPass) {
                $cookiesStringToPass .= ';';
            }
            $cookiesStringToPass .= $name . '=' . urlencode($value);
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookiesStringToPass);
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
    private function followRedirectAndGetFinalURL($url) {
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
    private function getImg($url) {
        $ch = $this->getCurlInstance($url);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

}
