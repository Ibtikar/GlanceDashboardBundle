<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\CausePage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Season;
use Ibtikar\GlanceDashboardBundle\Document\Slug;

class SeasonController extends BackendController {

    protected $translationDomain = 'season';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array(),
            "titleEn" => array(),
            "coverPhoto" => array("type" => "refereceVideo", 'isSortable' => FALSE),
            "createdAt" => array("type" => "date"),
            "updatedAt" => array("type" => "date"),
            "createdBy" => array("isSortable" => false),
            "updatedBy" => array("isSortable" => false)
        );
        $this->defaultListColumns = array(
            "title",
            "titleEn",
            'coverPhoto',
            'createdAt',
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array("Edit", "Delete"));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Season:list.html.twig");
    }

    protected function doList(Request $request) {
        $configParams = parent::doList($request);
        return $configParams;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request) {
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Season'),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Seasons')
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();

        $season = new Season();
        $form = $this->createFormBuilder($season, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('title', formType\TextType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('titleEn', formType\TextType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('description', formType\TextareaType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('descriptionEn', formType\TextareaType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('media', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
                ->add('save', formType\SubmitType::class)
                ->getForm();


        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $request->get('form');


                $dm->persist($season);

                $this->slugifier($season);
                $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                    'type' => 'video',
                    'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                    'product' => null,
                    'collectionType' => 'Season'
                ));

                $dm->flush();
                $this->updateMaterialGallary($season, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_season_list'), array(), true));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Season:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                    'title' => $this->trans('Add new Season', array(), $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request, $id) {
        $menus = array(array('type' => 'create', 'active' => true, 'linkType' => 'add', 'title' => 'Add new Season'),
            array('type' => 'list', 'active' => FALSE, 'linkType' => 'list', 'title' => 'list Seasons')
        );
        $breadCrumbArray = $this->preparedMenu($menus);
        $dm = $this->get('doctrine_mongodb')->getManager();

        $season = $dm->getRepository('IbtikarGlanceDashboardBundle:Season')->find($id);
        if (!$season) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }


        $form = $this->createFormBuilder($season, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('title', formType\TextType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('titleEn', formType\TextType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('description', formType\TextareaType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('descriptionEn', formType\TextareaType::class, array('required' => TRUE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('media', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
                ->add('save', formType\SubmitType::class)
                ->getForm();


        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $request->get('form');
                $dm->flush();
                $this->updateMaterialGallary($season, $formData['media'], $dm);

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

                return new JsonResponse(array('status' => 'redirect', 'url' => $this->generateUrl('ibtikar_glance_dashboard_season_list'), array(), true));
            }
        }


        return $this->render('IbtikarGlanceDashboardBundle:Season:edit.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb' => $breadCrumbArray,
                    'season' => $season,
                    'deletePopoverConfig' => array("question" => "You are about to delete %title%,Are you sure?"),
                    'title' => $this->trans('edit Season', array(), $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    public function slugifier($season) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($season->getTitle(), 100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($season->getTitleEn(), 100));
        $arabicCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Season')
                        ->field('deleted')->equals(FALSE)
                        ->field('slug')->equals($slugAr)
                        ->field('id')->notEqual($season->getId())->
                        getQuery()->execute()->count();

        $englishCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Season')
                        ->field('deleted')->equals(FALSE)
                        ->field('slugEn')->equals($slugEn)
                        ->field('id')->notEqual($season->getId())->
                        getQuery()->execute()->count();
        if ($arabicCount != 0) {
            $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($season->getTitle(), 100) . "-" . date('ymdHis'));
        }
        if ($englishCount != 0) {
            $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($season->getTitleEn(), 100) . "-" . date('ymdHis'));
        }

        $season->setSlug($slugAr);
        $season->setSlugEn($slugEn);

        $slug = new Slug();
        $slug->setReferenceId($season->getId());
        $slug->setType(Slug::$TYPE_SEASON);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);
        $dm->flush();
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
            $videos = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('id' => array('$in' => $imagesIds)));



            foreach ($videos as $mediaObj) {
                $image = $imagesData[$mediaObj->getId()];

                $mediaObj->setSeason($document);
                $mediaObj->setOrder($image['order']);
                $mediaObj->setCaptionAr($image['captionAr']);
                $mediaObj->setCaptionEn($image['captionEn']);
                $mediaObj->setDescriptionAr($image['descriptionAr']);
                $mediaObj->setDescriptionEn($image['descriptionEn']);
                if (isset($image['cover']) && $image['cover']) {
                    $document->setCoverPhoto($mediaObj);
                    $mediaObj->setCoverPhoto(TRUE);
                    $coverExist = TRUE;
                }
            }
//
            $dm->flush();
        }

        $dm->flush();
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
                        $oneDocument['actions'] = $this->renderView('IbtikarGlanceDashboardBundle:List:_listActions.html.twig', $templateVars);
                        continue;
                    }
                }
                $getfunction = "get" . ucfirst($value);
                if ($value == 'profilePhoto' || $value == 'coverPhoto') {
                    $image = $document->$getfunction();
                    if (!$image) {
                        $image = '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg';
                    } else {
                        if ($document->getCoverPhoto()->getType() == 'video') {
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
