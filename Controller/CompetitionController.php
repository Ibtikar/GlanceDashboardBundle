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

        $questionEn = new Question();
        $questionEn->addAnswer(new \Ibtikar\GlanceDashboardBundle\Document\QuestionChoiceAnswer);
        $questionEn->addAnswer(new \Ibtikar\GlanceDashboardBundle\Document\QuestionChoiceAnswer);
        $competition->getQuestionsEn()->add($questionEn);
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
            "createdAt" => array("type" => "date"),
            "questionsCount" => array(),
            "expiryDate" => array("type" => "date"),
            'noOfAnswer' => array(),
        );
        $this->defaultListColumns = array(
            "title",
            "createdAt",
            "questionsCount",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }

    protected function configureListParameters(Request $request) {
        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder("IbtikarGlanceDashboardBundle:Competition")
                ->field('status')->equals($this->status)
                ->field('deleted')->equals(false);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");

        $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Competition:list.html.twig");
    }

    protected function doList(Request $message) {
        $renderingParams = parent::doList($message);
        return $this->getTabCount($renderingParams);
    }

    public function getTabCount($renderingParams = array()) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $renderingParams['newCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
                        ->field('status')->equals(Competition::$statuses['new'])
                        ->getQuery()->execute()->count();
        $renderingParams['publishCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
                        ->field('status')->equals(Competition::$statuses['publish'])
                        ->getQuery()->execute()->count();
        $renderingParams['unpublishCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
                        ->field('status')->equals(Competition::$statuses['unpublish'])
                        ->getQuery()->execute()->count();
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


      public function publishAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $securityContext = $this->get('security.authorization_checker');
        $publishOperations = $this->get('recipe_operations');
        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_PUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {

            $this->addFlash('error', $this->trans('You are not authorized to do this action any more'));

            $result = array('status' => 'reload-table','message'=>$this->trans('You are not authorized to do this action any more'));
            return new JsonResponse($result);
        }

        if ($request->getMethod() === 'GET') {
            $id = $request->get('id');
            if (!$id) {
                return $this->getFailedResponse();
            }

            $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->findOneById($id);
            if (!$competition)
                throw $this->createNotFoundException($this->trans('Wrong id'));

            return $this->render('IbtikarGlanceDashboardBundle::publishModal.html.twig', array(
                    'goodyStar' => $competition->getGoodyStar(),
                    'displayGoodyStar' => TRUE,
                    'translationDomain' => $this->translationDomain,
                    'locations' => array(),
                    'currentLocations' => array(),
                    'document' => $competition
            ));
        } else if ($request->getMethod() === 'POST') {

            $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findOneById($request->get('documentId'));
            if (!$competition) {
                if ($type && $type == 'view') {
                    $this->addFlash('error', $this->trans('not done'));
                }
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }
            $locations = $request->get('publishLocation', array());
            if (!empty($locations)) {
                $locations = $dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('id' => array('$in' => $request->get('publishLocation'))));
            }

            $recipeStatus = $competition->getStatus();
            $status = $request->get('status');
            $goodyStar = $request->get('goodyStar');
            if ($status != $recipeStatus) {
                if ($type && $type == 'view') {
                    $this->addFlash('error', $this->trans('not done'));
                }
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }


            switch ($recipeStatus) {
                case 'new':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($competition, $locations, FALSE, $goodyStar);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->autoPublish($competition, $locations, $autoPublishDate, $goodyStar);
                    }
                    break;
                case 'publish':
                    $publishResult = $publishOperations->managePublishControl($competition, $locations, $goodyStar);
                    break;
                case 'deleted':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($competition, $locations, TRUE, $goodyStar);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->autoPublish($competition, $locations, $autoPublishDate, $goodyStar);
                    }
                    break;
                case 'autopublish':
                    if ($request->get('publishNow')) {
                        $publishResult = $publishOperations->publish($competition, $locations, FALSE, $goodyStar);
                    } else if ($request->get('autoPublishDate', '')) {
                        $autoPublishDateString = $request->get('autoPublishDate', '');
                        if (strlen(trim($autoPublishDateString)) > 0) {
                            try {
                                $autoPublishDate = new \DateTime($autoPublishDateString);
                            } catch (\Exception $e) {
                                $autoPublishDate = null;
                            }
                        }
                        $publishResult = $publishOperations->manageAutoPublishControl($competition, $locations, $autoPublishDate, $goodyStar);
                    }
                    break;
            }

            if ($type && $type == 'view') {
                $this->addFlash($publishResult['status'], $publishResult['message']);
            }
            $this->container->get('facebook_scrape')->update($competition);


            return new JsonResponse(array_merge($publishResult, $this->getTabCount()));
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
