<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Document\Media;
use Symfony\Component\HttpFoundation\File\File;
use Ibtikar\GlanceDashboardBundle\Form\Type\MediaType;

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

        if ($documentId && $documentId != 'null') {
            if ($collectionType === 'SubProduct') {
                $document = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->find($documentId);
                if (!$document) {
                    throw $this->createNotFoundException($this->trans('Wrong id'));
                }
                $response = $this->getInvalidResponseForSubProduct($documentId, $imageType,'upload');
                if ($response) {
                    return $response;
                }
                $fieldUpdate='Subproduct';
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
                            'collectionType' => $collectionType,
                            'ProfilePhoto' => TRUE
                        ));
                        break;
                    case 'coverPhoto':
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                            'type' => $type,
                            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                            'product' => null,
                            'subproduct' => null,
                            'collectionType' => $collectionType,
                            'coverPhoto' => TRUE
                        ));
                        break;
                }
                if ($document) {
                    return new JsonResponse(array('status' => 'reload'));
                }
            }


        }
        $media = new Media();
        $media->setType($type);
        $media->setCollectionType($collectionType);

        $media->setCreatedBy($this->getUser());
        if ($documentId && $documentId != 'null' && $fieldUpdate) {
            $functionName = "set$fieldUpdate";
            $media->$functionName($document);
        }

        $validationGroup = array($collectionType);
        $form = $this->createForm(MediaType::class, $media, array(
            'translation_domain' => $this->translationDomain,
            'validation_groups' => $validationGroup,
            'csrf_protection' => false
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
                        case 'coverPhoto':
                            $media->setCoverPhoto(TRUE);
                            if ($documentId && $documentId != 'null') {
                                $functionName = "get$fieldUpdate";
                                $media->$functionName()->setCoverPhoto($media);
                            }
                    }
                }
                $tempPath = $media->getTempPath();
                $media->setTempPath('');
                $dm->persist($media);
                $dm->flush();



                return new JsonResponse(array('status' => 'success', 'media' => $this->prepareMedia($media,$collectionType), 'message' => $this->trans('upload successfuly')));
            } else {

                $tempPath = $media->getTempPath();
            }
        }
        if ($tempPath) {
            $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
            $fileSystem->remove($media->getTempPath());
        }
        $error = $this->trans('failed operation');
        foreach ($form->getErrors() as $errorObject) {
            $error = $errorObject->getMessage();
            break;
        }
        return new JsonResponse(array('error' => $error), 400);
    }

    private function prepareMedia($media,$collectionType)
    {

        return array(
            'imageUrl' => $media->getWebPath(),
            'path' => $media->getPath(),
            'id' => $media->getId(),
            'type' => $media->getType(),
            'coverPhoto' => $media->getCoverPhoto(),
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
            $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
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
                    $dm->flush();
                    if ($tempUrlPath) {
                        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
                        $fileSystem->remove($tempUrlPath);
                    }
                    return new JsonResponse(array('status' => 'success', 'media' => $this->prepareMedia($media,$collectionType), 'message' => $this->trans('upload successfuly')));
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
        if (!$this->getUser()) {
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
                if ($document->getCoverPhoto()) {
                    $document->getProduct()->setCoverPhoto(NULL);
                }
                if ($document->getProfilePhoto()) {
                    $document->getProduct()->setProfilePhoto(NULL);
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

        if ($collectionType == 'Recipe' && $document->getRecipe()) {
//            $response = $this->getInvalidResponseForRecipe($document->getRecipe()->getId(), $request->get('room'));
//            if ($response) {
//                return $response;
//            }
            if ($document->getCoverPhoto()) {
                return $this->getNotificationResponse(null, array(), 'error');
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
        if ($documentId && $documentId != 'null') {
            if ($collectionType === 'Product') {
                $reponse = $this->getInvalidResponseForProduct(new \MongoId($documentId), '', 'list');
                if ($reponse) {
                    return $reponse;
                }
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type,
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => new \MongoId($documentId),
                'subproduct' => null,
                'collectionType' => $collectionType
            ));
            }
            if ($collectionType === 'SubProduct') {
                $reponse = $this->getInvalidResponseForSubProduct(new \MongoId($documentId), '', 'list');
                if ($reponse) {
                    return $reponse;
                }
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type,
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'subproduct' => new \MongoId($documentId),
                'product' => null,
                'collectionType' => $collectionType
            ));
            }
            if ($collectionType === 'Recipe') {
                $reponse = $this->getInvalidResponseForRecipe($documentId, $this->container->get('request_stack')->getCurrentRequest()->get('room'));
                if ($reponse) {
                    return $reponse;
                }
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type == "all"?array('$in'=>array('image','video')):$type,
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'recipe' => new \MongoId($documentId),
                'subproduct' => null,
                'product' => null,
                'collectionType' => $collectionType
            ));
            }
        } else {
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type == "all"?array('$in'=>array('image','video')):$type,
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => null,
                'recipe' => null,
                'subproduct' => null,
                'collectionType' => $collectionType
            ));
        }

        $files = array();
        $coverPhoto = '';
        $profilePhoto = '';

        /* @var $document Media */
        foreach ($documents as $document) {
            if ($document->getCoverPhoto() && $collectionType!='Recipe') {
                $coverPhoto = $this->prepareMedia($document,$collectionType);
                continue;
            }
            if ($document->getProfilePhoto()) {
                $profilePhoto = $this->prepareMedia($document,$collectionType);
                continue;
            }
            if($document->getType()=='video'){
                $files [] = $this->getVideoArray($document, $documentId, $collectionType);

            }else{
               $files [] = $this->getMediaDataArray($document, $documentId, $collectionType);

            }
        }

        return new JsonResponse(array('images' => $files, 'coverPhoto' => $coverPhoto, 'profilePhoto' => $profilePhoto));
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
        $data = array(
            'imageUrl' => $media->getWebPath(),
            'id' => $media->getId(),
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_delete', array('id' => $media->getId(),'collectionType'=>$collectionType)),
            'cropUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_crop', array('id' => $media->getId(),'collectionType'=>$collectionType)),
            'pop' => str_replace('%title%', $this->trans('image', array(), $this->translationDomain), $this->get('app.twig.popover_factory_extension')->popoverFactory([])),
            'captionAr' => $media->getCaptionAr()?$media->getCaptionAr():"",
            'captionEn' => $media->getCaptionEn()?$media->getCaptionEn():"",
            'path' => $media->getPath(),
            'type' => $media->getType(),
            'cover'=>$media->getCoverPhoto()?'checked':''
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
                case 'coverPhoto':
                    $document = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                        'type' => 'image',
                        'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                        'product' => null,
                        'product' => new \MongoId($documentId),
                        'collectionType' => 'Product',
                        'coverPhoto' => TRUE
                    ));
                    break;
            }
            if ($document && $type=='upload') {
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

        if ($imagesize[0] < 200 || $imagesize[1] < 200) {
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
            'message' => '',
            'files' => &$files,
            'success' => &$successIds,
            'errors' => array()
        );
        if ($request->getMethod() === 'POST') {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $images = $request->get('images');
            $documentDir = __DIR__ . '/../../../../web/uploads/' . strtolower($collectionType) . '-file/google/' . $this->getUser()->getId() . '/';
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
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarAppBundle:' . $collectionType)->find($documentId);
                    $collectionSetter = "set" . $collectionType;
                    $media->$collectionSetter($document);
                    $lastPic = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder($this->getObjectShortName())
                            ->field(strtolower($collectionType))->equals($documentId)
                            ->field('type')->equals('image')
                            ->sort('order', 'DESC')
                            ->getQuery()->getSingleResult();
                    $order = 0;
                    if ($lastPic) {
                        $order = $lastPic->getOrder() + 1;
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
                    $errors = $validator->validate($media);
                } else {
                    $errors = $validator->validate($media);
                }

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $data['errors'][$error->getMessage()][] = $url;
                    }
                } else {
                    $dm->persist($media);
                    $dm->flush();

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
                $video = explode('#', $video);
                $vid = $video[0];
                $videoObj->setVid($vid);
                if ($request->get('collectionType')) {
                    $videoObj->setCollectionType($request->get('collectionType'));
                }
                if ($documentId && $documentId != 'null') {
                    if ($request->get('collectionType') == 'Recipe') {
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
                    } else {
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
                $videoObj->setCreatedBy($this->getUser());
                $videoObj->setType($type);
                $dm->persist($videoObj);
                $dm->flush();
                $data['message']=  $this->trans('upload successfuly');
                $data['video']=$this->getVideoArray($videoObj, null,$request->get('collectionType'));
            }
        }
        return new JsonResponse($data);
    }


    private function getVideoArray($video, $documentId = null,$collectionType='Recipe') {
        $routeParameters = array('id' => $video->getId());
        if($documentId) {
            $routeParameters['materialId'] = $documentId;
            $routeParameters['collectionType'] = $collectionType;
        }
        $data = array(
            'id' => $video->getId(),
            'vid' => $video->getVid(),
            'imageUrl'=>'https://i.ytimg.com/vi/' . $video->getVid() . '/hqdefault.jpg',
            'captionAr' => is_null($video->getCaptionAr())?"":$video->getCaptionAr(),
            'captionEn' => is_null($video->getCaptionEn())?"":$video->getCaptionEn(),
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_video_delete', $routeParameters),
            'type' => $video->getType(),
            'cover'=>$video->getCoverPhoto()?'checked':''
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
        return $this->getNotificationResponse($this->trans('Deleted sucessfully.'));
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
        $dm = $this->get('doctrine_mongodb')->getManager();
        $material = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->find($documentId);
        if (!$material) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $securityContext = $this->get('security.authorization_checker');
        if (!$room || (!$securityContext->isGranted('ROLE_' . strtoupper($room) . '_EDIT') && !$securityContext->isGranted('ROLE_ADMIN'))) {

            return $this->getAccessDeniedResponse();
        }

    }

}
