<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\SubProduct;
use Ibtikar\GlanceDashboardBundle\Document\Media;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Form\Type\SubProductType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;

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
         if ($request->get('type')) {
            $queryBuilder->field('type')->in($request->get('type'));
        }
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
        $medias = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('product'=>$product->getId()));
        $coverPhotos=array();
        $activityPhotos=array();
        $relatedTips=array();
        $relatedArticles=array();

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
        $naturalPhoto = NULL;
        $bannarPhoto = NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'type' => 'image',
            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
            'subproduct' => null,
            'product' => null,
            'collectionType' => 'SubProduct'
        ));
        foreach ($images as $image) {
            if ($image->getProfilePhoto()) {
                $profileImage = $image;
                continue;
            }
            if ($image->getNaturalPhoto()) {
                $naturalPhoto = $image;
                continue;
            }
            if ($image->getBannerPhoto()) {
                $bannarPhoto = $image;
                continue;
            }
        }
        $product = '';
        if ($request->get('productId')) {
            $product = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->find($request->get('productId'));
        }
        if (!$product) {
            throw $this->createNotFoundException();
        }
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct', 'link' => $this->generateUrl('ibtikar_glance_dashboard_subproduct_create', array('productId' => $product->getId()))),
            array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Activity', 'link' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $product->getId()))));
        $breadCrumbArray = $this->preparedMenu($menus);
        $subProduct = new SubProduct();

        $form = $this->createForm(SubProductType::class, $subProduct, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData=$request->get('sub_product');
                $subProduct->setProduct($product);
                $dm->persist($subProduct);
                $this->slugifier($subProduct);

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
                    if ($image->getNaturalPhoto()) {
                        $subProduct->setNaturalPhoto($image);
                        $image->setSubproduct($subProduct);
                        continue;
                    }
                }

                $dm->flush();
                $this->updateMaterialGallary($subProduct, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:SubProduct:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'profileImage' => $profileImage,
                    'naturalPhoto' => $naturalPhoto,
                    'bannarPhoto' => $bannarPhoto,
                    'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                    'title' => $this->trans('Add new subProduct', array(), $this->translationDomain),
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
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new subProduct', 'link' => $this->generateUrl('ibtikar_glance_dashboard_subproduct_create', array('productId' => $subproduct->getProduct()->getId()))),
            array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Activity', 'link' => $this->generateUrl('ibtikar_glance_dashboard_activity_create', array('productId' => $subproduct->getProduct()->getId()))));
        $breadCrumbArray = $this->preparedMenu($menus);
        $form = $this->createForm(SubProductType::class, $subproduct, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));
        $profileImage = NULL;
        $naturalPhoto = NULL;
        $bannarPhoto = NULL;
        $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'type' => 'image',
            'createdBy.$id' => new \MongoId($this->getUser()->getId()),
            'subproduct' => $subproduct->getId(),
            'product' => null,
            'collectionType' => 'SubProduct'
        ));
        foreach ($images as $image) {
            if ($image->getProfilePhoto()) {
                $profileImage = $image;
                continue;
            }
            if ($image->getNaturalPhoto()) {
                $naturalPhoto = $image;
                continue;
            }
            if ($image->getBannerPhoto()) {
                $bannarPhoto = $image;
                continue;
            }
        }

        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData=$request->get('sub_product');

                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                $this->updateMaterialGallary($subproduct, $formData['media'], $dm);
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:SubProduct:edit.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'profileImage' => $subproduct->getProfilePhoto(),
                    'naturalPhoto' => $naturalPhoto,
                    'bannarPhoto' => $bannarPhoto,
                    'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
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
                    if($document->getType()=='subproduct'){
                        $image = $document->getCoverPhoto();
                    }else{
                        $image = $document->$getfunction();

                    }
                    if (!$image) {
                        $image = '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg';
                    } else {
                        if ($document->getProfileType() == 'video') {
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

    public function deleteAction(Request $request) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_DELETE') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'), 'count' => $this->getDocumentCount());
            return new JsonResponse($result);
        }
        $id = $request->get('id');
        if (!$id) {
            return $this->getFailedResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document || $document->getDeleted()) {
            return new JsonResponse(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation'), 'count' => $this->getDocumentCount()));
        }

        $errorMessage = $this->validateDelete($document);

        if ($errorMessage || is_null($document)) {
            return $this->getFailedAlertResponse($errorMessage);
        }

        try {
            $id = $document->getId();
            $productId = $document->getProduct()->getId();
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

        return new JsonResponse(array('status' => 'success', 'message' => $this->get('translator')->trans('done sucessfully'), 'count' => $count));
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
        $message = str_replace(array('%action%', '%item-translation%', '%ids-count%'), array($translator->trans($bulkAction), $this->trans(strtolower($this->oneItem != "" ? $this->oneItem : $this->calledClassName)), count($ids)), $translator->trans('successfully %action% %success-count% %item-translation% from %ids-count%.'));
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
            $data['count'] = $this->getDocumentCount();
            return new JsonResponse($data);
        }

        switch ($bulkAction) {
            case 'Delete':

                $permission = 'ROLE_' . strtoupper($this->calledClassName) . '_DELETE';

                if (!$securityContext->isGranted($permission) && !$securityContext->isGranted('ROLE_ADMIN')) {
                    $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'), 'count' => $this->getDocumentCount());
                    return new JsonResponse($result);
                }

                $bulkQueries = array(
                    'readLater' => array(),
                    'likes' => array()
                );

                foreach ($documents as $document) {
                    $productId = $document->getProduct()->getId();
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

        $data['count'] = $dm->createQueryBuilder($this->getObjectShortName())
                ->field('deleted')->equals(FALSE)
                ->field('product')->equals($productId)
                ->getQuery()
                ->count();

        $data['message'] = str_replace('%success-count%', count($successIds), $message);
        return new JsonResponse($data);
    }

    public function updateMaterialGallary($document, $gallary, $dm = null) {
        if (!$dm) {
            $dm = $this->get('doctrine_mongodb')->getManager();
        }

        $gallary = json_decode($gallary, true);

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
                  $mediaObj->setCoverPhoto(FALSE);
                if (isset($image['cover']) && $image['cover']) {
                    $document->setCoverPhoto($mediaObj);
                    $mediaObj->setCoverPhoto(TRUE);
                    $coverExist = TRUE;
                }
                $mediaObj->setSubproduct($document);
                $mediaObj->setOrder($image['order']);
                $mediaObj->setCaptionAr($image['captionAr']);
                $mediaObj->setCaptionEn($image['captionEn']);
            }
//
            $dm->flush();
        }

        $dm->flush();
    }

    public function slugifier($subproduct) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($subproduct->getName(), 100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($subproduct->getNameEn(), 100));
        $arabicCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:SubProduct')
                ->field('deleted')->equals(FALSE)
                ->field('slug')->equals($slugAr)
                ->field('id')->notEqual($subproduct->getId())->
                getQuery()->execute()->count();

        $englishCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:SubProduct')
                ->field('deleted')->equals(FALSE)
                ->field('slugEn')->equals($slugEn)
                ->field('id')->notEqual($subproduct->getId())->
                getQuery()->execute()->count();
        if ($arabicCount != 0) {
            $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($subproduct->getName(), 100) . "-" . date('ymdHis'));
        }
        if ($englishCount != 0) {
            $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($subproduct->getNameEn(), 100) . "-" . date('ymdHis'));
        }

        $subproduct->setSlug($slugAr);
        $subproduct->setSlugEn($slugEn);

        $slug = new Slug();
        $slug->setReferenceId($subproduct->getId());
        $slug->setType(Slug::$TYPE_SUBPRODUCT);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);
        $dm->flush();
    }


    public function contentCountAction(Request $request)
    {
        $renderingParams['draftRecipeCountRecipe'] = $this->buildQueryBuilder($request)->field('type')->equals('subproduct')->getQuery()->execute()->count();
        $renderingParams['draftRecipeCountArticle'] = $this->buildQueryBuilder($request)->field('type')->equals(SubProduct::$TypeChoices['activity'])->getQuery()->execute()->count();
        $renderingParams['draftRecipeCountTip'] = $this->buildQueryBuilder($request)->field('type')->equals(SubProduct::$TypeChoices['bestProduct'])->getQuery()->execute()->count();
        return new JsonResponse($renderingParams);
    }


    public function buildQueryBuilder(Request $request) {
        return $this->get('doctrine_mongodb')->getManager()->createQueryBuilder("IbtikarGlanceDashboardBundle:SubProduct")->field('product')->equals(new \MongoId($request->get('id')))
                        ->field('deleted')->equals(false);
    }

}
