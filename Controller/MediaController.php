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
        $media = new Media();
        $media->setType($type);
        $media->setCollectionType($collectionType);
        if ($documentId && $documentId != 'null') {

        }
        $media->setCreatedBy($this->getUser());


        $validationGroup = array($type);
        $form = $this->createForm(MediaType::class, $media, array(
            'translation_domain' => $this->translationDomain,
            'validation_groups' => $validationGroup,
            'csrf_protection' => false
        ));
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $imageType = $request->get('imageType');
                if ($imageType) {

                    switch ($imageType) {
                        case 'profile':
                            $media->setProfilePhoto(TRUE);
                            break;
                        case 'coverPhoto':
                            $media->setCoverPhoto(TRUE);
                    }
                }
                $dm = $this->get('doctrine_mongodb')->getManager();
                $tempPath = $media->getTempPath();
                $media->setTempPath('');
                $dm->persist($media);
                $dm->flush();



                return new JsonResponse(array('status' => 'success', 'media' => $this->prepareMedia($media), 'message' => $this->trans('upload successfuly')));
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

    private function prepareMedia($media)
    {
        return array(
            'imageUrl' => $media->getWebPath(),
            'id' => $media->getId(),
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_delete', array('id' => $media->getId())),
            'cropUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_crop', array('id' => $media->getId())),
            'pop' => str_replace('%title%', $this->trans('image', array(), $this->translationDomain), $this->get('app.twig.popover_factory_extension')->popoverFactory([]))
        );
    }

    public function cropAction(Request $request, $id)
    {

        $media = $request->get('media');
        if (isset($media['file']) && $media['file']) {
            $fileData = explode('base64,', $media['file']);
            $imageString = base64_decode($fileData[1]);
            $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
            if ($imageString) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $media = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->find($id);
                if (!$media) {
                    return new JsonResponse('status', 'refresh');
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
                    return new JsonResponse(array('status' => 'success', 'media' => $this->prepareMedia($media), 'message' => $this->trans('done sucessfully')));
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
    public function deleteFileAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        /* @var $document Media */
        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document) {
            return $this->getNotificationResponse(null, array('deleted' => true), 'error');
        }

        $documentId = $request->get('documentId');
        if ($documentId && $documentId != 'null') {
            $collectionType = $request->get('collectionType');

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
    public function userFilesAction($type, $documentId = NULL, $collectionType = 'Material')
    {
        if ($documentId && $documentId != 'null') {

        } else {
            $documents = $this->get('doctrine_mongodb')->getManager()->getRepository($this->getObjectShortName())->findBy(array(
                'type' => $type,
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'product' => null,
                'collectionType' => $collectionType
            ));
        }

        $files = array();
        $coverPhoto='';
        $profilePhoto='';

        /* @var $document Media */
        foreach ($documents as $document) {
            if($document->getCoverPhoto()){
                $coverPhoto= $this->prepareMedia($document);
                continue;
            }
            if($document->getProfilePhoto()){
                $profilePhoto=$this->prepareMedia($document);
                continue;
            }
            $files [] = $this->getMediaDataArray($document, $documentId, $collectionType);
        }

        return new JsonResponse(array('images'=>$files,'coverPhoto'=>$coverPhoto,'profilePhoto'=>$profilePhoto));
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Media $media
     * @param string|null $documentId
     * @param string|null $collectionType
     * @return array
     */
    private function getMediaDataArray(Media $media, $documentId = null, $collectionType = null)
    {

        $data = array(
           'imageUrl' => $media->getWebPath(),
            'id' => $media->getId(),
            'deleteUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_delete', array('id' => $media->getId())),
            'cropUrl' => $this->generateUrl('ibtikar_glance_dashboard_media_crop', array('id' => $media->getId())),
            'pop' => str_replace('%title%', $this->trans('image', array(), $this->translationDomain), $this->get('app.twig.popover_factory_extension')->popoverFactory([]))
        );
        return $data;
    }

    /**
     * @author ahmad Gamal<a.gamal@ibtikar.net.sa>
     */
    public function upload_imageUrlAction(Request $request)
    {
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
                    if ($collectionType == 'City') {
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarBackendBundle:' . $collectionType)->find($documentId);
                    } elseif ($collectionType == 'Place') {
                        $document = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarBackendBundle:' . $collectionType)->find($documentId);
                        $documentMedia = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarAppBundle:Media')->findOneBy(array('place' => $documentId));
                        if ($documentMedia) {
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
    private function getCurlInstance($url)
    {
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
}
