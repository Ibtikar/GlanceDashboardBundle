<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Form\Type\CompetitionType;
use Ibtikar\GlanceDashboardBundle\Document\Competition;
use Ibtikar\GlanceDashboardBundle\Document\Question;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * Description of CompetitionController
 *
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class CompetitionController extends BackendController {

    protected $translationDomain = 'competition';
    protected $calledClassName = 'Competition';

    protected function getObjectShortName() {
        return 'IbtikarGlanceDashboardBundle:' . $this->calledClassName;
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request) {
        $competition = new Competition();
        $question = new Question();
        $question->addAnswer(new \Ibtikar\GlanceDashboardBundle\Document\QuestionChoiceAnswer);
        $question->addAnswer(new \Ibtikar\GlanceDashboardBundle\Document\QuestionChoiceAnswer);
        $competition->getQuestions()->add($question);
//        $competition->getQuestions()->add(new Question());

        $form = $this->createForm(CompetitionType::class, $competition, array('translation_domain' => $this->translationDomain,
                'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($competition);
                $dm->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Competition:create.html.twig', array(
                    'form' => $form->createView(),
                    'title' => $this->trans('Add new Competition', array(), $this->translationDomain),
                    'form_theme' => 'IbtikarGlanceDashboardBundle:Competition:form_theme_competition.html.twig',
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request, $id) {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem('backend-home', $this->generateUrl('backend_home'));
        $breadcrumbs->addItem('List Competition', $this->generateUrl('competition_list'));
        $breadcrumbs->addItem('edit competition', $this->generateUrl('competition_edit', array('id' => $id)));


        $dm = $this->get('doctrine_mongodb')->getManager();
        $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($id);

            if (!$competition){
                throw $this->createNotFoundException($this->trans('Wrong id'));
            }

        $form = $this->createForm(new CompetitionType($competition->getStatus() == "new"?true:false), $competition, array('translation_domain' => $this->translationDomain));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Competition:edit.html.twig', array(
                    'form' => $form->createView(),
                    'form_theme' => 'IbtikarGlanceDashboardBundle:Competition:form_theme_competition.html.twig',
                    'translationDomain' => $this->translationDomain
        ));
    }

    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array(),
            "expiryDate" => array("type" => "date"),
            "createdBy" => array("isSortable" => false),
            "createdAt" => array("type" => "date"),
            "status" => array("type" => "translated"),
            "publishedAt" => array("type" => "date"),
            "publishedBy" => array("isSortable" => false),
        );
        $this->defaultListColumns = array(
            "title",
            "expiryDate",
            "createdBy",
            "createdAt",
            "status",
            "publishedAt",
            "publishedBy",
        );
    }

    protected function configureListParameters(Request $request) {
        $queryBuilder = $this->createQueryBuilder("IbtikarGlanceDashboardBundle")
                        ->field('deleted')->equals(false);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array('Add','Search', 'Publish_Unpublish','Delete','AutoPublish','AutoPublishControl', 'Edit','ViewOne'));
        $this->listViewOptions->setBulkActions(array("Delete"));
        if ($request->get('title')) {
            $queryBuilder = $queryBuilder->field('title')->equals(new \MongoRegex(('/' . preg_quote(trim($request->get('title'))) . '/i')));
        }
        if ($request->get('status')) {
            $queryBuilder = $queryBuilder->field('status')->equals($request->get('status'));
        }
        if ($request->get('createdBy')) {
            $queryBuilder->field('createdBy')->equals($this->getStaffByFullname($request->get('createdBy')));
        }
        if ($request->get('publishedBy')) {
            $queryBuilder->field('publishedBy')->equals($this->getStaffByFullname($request->get('publishedBy')));
        }

        if ($request->get('from') && (bool) strtotime($request->get('from'))) {
            $queryBuilder = $queryBuilder->field('publishedAt')->gte(new \DateTime($request->get('from')));
        }
        if ($request->get('to') && (bool) strtotime($request->get('to'))) {
            $fromDate = new \DateTime($request->get('to'));
            $queryBuilder->field('publishedAt')->lte($fromDate->modify('+1 day'));
        }
        $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Competition:list.html.twig");
    }

    protected function doList(Request $request) {
        $renderingParams = parent::doList($request);

        $dm = $this->get('doctrine_mongodb')->getManager();

        $renderingParams['search'] = FALSE;

        $parameters = $request->query->all();

//        $renderingParams['filterLink'] = $this->generateUrl('poll_list', array('status' => '2-autopublish-poll', 'sort' => 'publishedAt', 'direction' => 'desc', 'page' => 1));
//        $renderingParams['filterLinkToolTip'] = 'Autopublished';

        if ($request->get('status') || $request->get('createdBy') || $request->get('publishedBy') || $request->get('from') || $request->get('to') || $request->get('title')) {
            $renderingParams['search'] = TRUE;
        }


        return $renderingParams;
    }


    public function updatePublishAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $securityContext = $this->get('security.authorization_checker');
        $id = $request->get('id');
        if (!$id) {
            return $this->getFailedResponse();
        }
        $publish = $request->get('publish');

        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH_UNPUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('You are not authorized to do this action any more'));
            return new JsonResponse(array('status' => 'reload-page'), 403);
        }

        $competition = $dm->getRepository($this->getObjectShortName())->findOneBy(array('id' => $id, 'deleted' => false));
        if (!$competition) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('failed operation'));
            return $this->getFailedResponse('failed-reload');
        }
        if ($publish === 'true') {
            $setPublish = true;
            $errorMessage = $this->validatePublish($competition, $publish);
        } else {
            $setPublish = false;
            $errorMessage = $this->validateUnpublish($competition, $publish);

        }
        if ($errorMessage) {
            return $this->getFailedAlertResponse($errorMessage);
        }
        if ($setPublish) {
            if ($competition->getStatus() == "new") {
                $this->publish($competition, $dm);
            } else if ($competition->getStatus() == "autopublish") {
                $competition->setAutoPublishDate(null);
                $this->publish($competition, $dm);
            } else {
                $newCompetition = clone $competition;
                $dm->persist($newCompetition);
                $this->publish($newCompetition, $dm);
            }
        } else {
            $this->changePublish($competition, $setPublish);
        }
        $dm->flush();

        $successMessage = $this->get('translator')->trans('done sucessfully');
        $this->get('session')->getFlashBag()->add('success', $successMessage);
        return new JsonResponse(array('status' => 'success', 'message' => $successMessage));
    }


    protected function validatePublish(Document $document, $publish) {
        if ($document->getStatus() == 'published' && $publish === 'true') {
            return $this->trans('Already published');
        }
        if ($document->getExpiryDate() && $document->getExpiryDate() < new \DateTime()) {
            return $this->trans('Date expired');
        }
    }

    protected function publish(Competition $competition, $dm) {

        $competition->setStatus(Competition::$statuses['published'])
             ->setPublishedAt(new \DateTime())
             ->setPublishedBy($this->getUser());
        return true;
    }

    protected function validateDelete(Document $document) {
        if ($document->getStatus() == Competition::$statuses['deleted']) {
            return $this->get('translator')->trans('failed operation');
        }
    }


    public function autoPublishDateAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $securityContext = $this->get('security.authorization_checker');

        if ($request->getMethod() === 'GET') {

            if (!$securityContext->isGranted('ROLE_COMPETITION_AUTOPUBLISH') && !$securityContext->isGranted('ROLE_ADMIN') && $request->get('action') == "autoPublishControl" ) {
                $this->get('session')->getFlashBag()->add('error', $this->trans('You are not authorized to do this action any more'));
                $result = array('status' => 'reload-page');
                return new JsonResponse($result, 403);
            }
        if (!$securityContext->isGranted('ROLE_COMPETITION_AUTOPUBLISH') && !$securityContext->isGranted('ROLE_ADMIN') && $request->get('action') == "autoPublish" ) {
                $this->get('session')->getFlashBag()->add('error', $this->trans('You are not authorized to do this action any more'));
                $result = array('status' => 'reload-page');
                return new JsonResponse($result, 403);
            }

            $id = $request->get('id');
            if (!$id) {
                return $this->getFailedResponse();
            }

            $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($id);
            if (!$competition)
                throw $this->createNotFoundException($this->trans('Wrong id'));


            $autoPublishDate = '';
            $autoPublishTime = '12:00 AM';
            if($competition->getAutoPublishDate()) {
                $autoPublishDate = $competition->getAutoPublishDate()->format('Y-m-d');
                $autoPublishTime = $competition->getAutoPublishDate()->format('H:i A');
            }

            return $this->render('IbtikarGlanceDashboardBundle::publishModal.html.twig', array(
                        'type'=>'competition',
                        'autoPublishDate' => $autoPublishDate,
                        'autoPublishTime' => $autoPublishTime,
                        'translationDomain' => $this->translationDomain,
                        'locations' => '',
                        'currentLocations' => '',
                        'homeLocations' => '',
                        'portalLocations' => '',
                        'questionaireAutopublish'=>true
            ));
        } else if ($request->getMethod() === 'POST') {
            $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($request->get('pollId'));
            $autoPublishDate = null;
                $autoPublishDateString = $request->get('autoPublishDate', '');
                if (strlen(trim($autoPublishDateString)) > 0) {
                    try {
                        $autoPublishDate = new \DateTime($autoPublishDateString);
                    } catch (\Exception $e) {
                        $autoPublishDate = null;
                    }
                }

        if ($competition->getStatus() == Competition::$statuses['autopublish'] && $request->get('action') == "autoPublishControl") {
                if (!$securityContext->isGranted('ROLE_COMPETITION_AUTOPUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {
                    $this->get('session')->getFlashBag()->add('error', $this->container->get('translator')->trans('Sorry no longer have permission to complete this process', array(), 'room'));

                    $result = array('status' => 'reload-page');
                    return new JsonResponse($result, 403);
                }

                if (!($autoPublishDate instanceof \DateTime) || $autoPublishDate < new \DateTime()) {
                    return new JsonResponse(array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a publish date after today')));
                }
                if ($competition->getExpiryDate() && $autoPublishDate > $competition->getExpiryDate()) {
                    return new JsonResponse(array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a date before expired date')));
                }
                $competition->setPublishedBy($this->getUser());

                $competition->setAutoPublishDate($autoPublishDate);
                $competition->setPublishedAt($autoPublishDate);

//                $publishResult = $this->get('MaterialOperations')->manageAutoPublishControl($poll, $locations,new \DateTime($request->get('autoPublishDate')));
            } elseif ($request->get('action') == "autoPublish") {
                if (!$securityContext->isGranted('ROLE_COMPETITION_AUTOPUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {
                    $this->get('session')->getFlashBag()->add('error', $this->container->get('translator')->trans('Sorry no longer have permission to complete this process', array(), 'room'));

                    $result = array('status' => 'reload-page');
                    return new JsonResponse($result, 403);
                }
                if ($competition->getStatus() == Competition::$statuses['autopublish']) {
                    $this->get('session')->getFlashBag()->add('error', $this->container->get('translator')->trans('Sorry this poll has already been published'));
                    return new JsonResponse(array("status" => "success", "message" => $this->container->get('translator')->trans('Sorry this poll has already been published')));
                }

                if (!($autoPublishDate instanceof \DateTime) || $autoPublishDate < new \DateTime()) {
                    return new JsonResponse(array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a publish date after today')));
                }
                if ($competition->getExpiryDate() && $autoPublishDate > $competition->getExpiryDate()) {
                    return new JsonResponse(array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a date before expired date')));
                }


                if ($competition->getStatus() == "new") {
                    $competition->setStatus(Competition::$statuses['autopublish']);
                    $competition->setPublishedBy($this->getUser());

                    $competition->setAutoPublishDate($autoPublishDate);
                    $competition->setPublishedAt($autoPublishDate);
                } else {
                    $newCompetition = clone $competition;
                    $dm->persist($newCompetition);
                    $newCompetition->setStatus(Competition::$statuses['autopublish']);
                    $newCompetition->setPublishedBy($this->getUser());
                    $newCompetition->setAutoPublishDate($autoPublishDate);
                    $newCompetition->setPublishedAt($autoPublishDate);
                }
            }
            $dm->flush();

            $this->get('session')->getFlashBag()->add('success', ($request->get('action') == "autoPublish" || $request->get('action') == "autoPublishControl" ? $this->get('translator')->trans(($request->get('action') == "autoPublishControl"?"new ":"").'settings saved and competition will be published at %datetime%', array('%datetime%' => $autoPublishDate->format('Y-m-d h:i A'))) : $this->get('translator')->trans('done sucessfully')));

            return new JsonResponse(array("status"=>'success',"message"=>'true'));
        }
    }

    public function viewAction(Request $request,$id)
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem('backend-home', $this->generateUrl('backend_home'));
        $breadcrumbs->addItem('List Competition', $this->generateUrl('competition_list'));
        $breadcrumbs->addItem('view competition');
        $dm = $this->get('doctrine_mongodb')->getManager();

        $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($id);
        if (!$competition){
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $questions = $competition->getQuestions();
        $drawChart=array();
        foreach ($questions as $index => $question) {
            switch ($question->getQuestionType()) {
                case "multiple-answer":
                case "single-answer":
                $answerPieChart=array();
               foreach ($question->getAnswers() as $index => $answer) {
                        $key = $index + 1;
                        $answerPieChart[] = array("value" => $answer->getPercentage(),
                            "color" => Competition::$COMPETITION_ANSWER_Highlighted_COLORS["color$key"],
                            "label" => $this->trans("answer $key"));
                    }
                    $drawChart[$question->getId()]=json_encode($answerPieChart);


                    break;

                case "text":
                    $elementType = "text";
                    $elementParams['attr'] = array(
                        'placeholder' => $question->getQuestion()
                    );
                case "date":
                    $elementType = "date";
                    break;
                case "phone":
                    $elementType = "phone";
                case "email":
                    break;
                case "textarea":
                    $elementType = "textarea";
                    $elementParams['attr'] = array(
                        'placeholder' => $question->getQuestion()
                    );
                    break;
//
//                        $formBuilder->add('word', 'text', array('label' => $this->numberToHtmlArabicCharacters('106')." - ".'سؤال منو مفهوم' ,'required' => true, 'constraints' => array(new \Symfony\Component\Validator\Constraints\NotBlank()),
//                               'attr' => array('placeholder' => 'سؤال منو مفهوم')
//                            ));
//                        $formBuilder->add('word3', 'choice', array('required' => true, 'choices' => array(
//                           'kambosho' => 'ambosho',
//                           'kambosho2' => 'ambosho3',
//                           'kambosho6' => 'ambosho5',
//                   ),'expanded' => true,
//                                'attr' => array(
//                                    'answers-wrapper-class' => 'horizontal-answers',
//                                   )
//                                ));
//                        $formBuilder->add('wordy', 'choice', array('required' => true, 'choices' => array(
//                           'kambosho' => 'ambosho',
//                           'kambosho2' => 'ambosho3',
//                           'kambosho6' => 'ambosho5',
//                   ),'expanded' => true,'multiple' => true));
//                   break;
                default:
                    break;
            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Competition:view.html.twig', array(
                'translationDomain' => $this->translationDomain,
                'competition' => $competition,
                'drawChart' => $drawChart
        ));
    }

    public function answerListAction(Request $request, $id,$type) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return $this->getLoginResponse();
        }
        if (!$securityContext->isGranted('ROLE_COMPETITION_CREATE') && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:QuestionAnswer');
        $queryBuilder->field('question')->equals(new \MongoId($id))
                ->sort('createdAt', 'desc');

        $query = $queryBuilder->getQuery();

        $limit = $request->get('limit');
        if (!$limit || !in_array($limit, array(10, 20, 50))) {
            $limit = $this->container->getParameter('per_page_items');
        }

        $pageNumber = $request->query->get('page', 1);

        if ($pageNumber < 1) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $query, $pageNumber /* page number */, $limit /* limit per page */
        );

        $items = $pagination->getItems();
        if (!$request->isXmlHttpRequest() && empty($items) && $pagination->getCurrentPageNumber() != 1) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        if ($request->isXmlHttpRequest() && empty($items) && $pagination->getCurrentPageNumber() != 1) {
            $pageNumber = $pageNumber - 1;
            $pagination = $paginator->paginate($query, $pageNumber, $limit);
        }
        $sortBy = 'createdAt';
        $sortOrder = 'desc';
        if (is_null($request->get('sort'))) {
            $pagination->setParam('sort', $sortBy);
            $pagination->setParam('direction', $sortOrder);
        }


        $renderingParams['pageNumber'] = $pageNumber;
        $renderingParams['pagination'] = $pagination;
        $renderingParams['paginationData'] = $pagination->getPaginationData();
        $renderingParams['translationDomain'] = 'competition';
        $renderingParams['type'] = $type;
        return $this->render('IbtikarGlanceDashboardBundle:Competition:answersList.html.twig', $renderingParams);
    }
}
