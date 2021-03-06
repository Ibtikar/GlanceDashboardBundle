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
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of CompetitionController
 *
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class CompetitionController extends BackendController {

    protected $translationDomain = 'competition';

    protected function getObjectShortName() {
        return 'IbtikarGlanceDashboardBundle:Competition';
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

        $coverImage = NULL;
        $coverVideo = NULL;

        $mediaList = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                'createdBy.$id' => new \MongoId($this->getUser()->getId()),
                'competition' => null,
                'collectionType' => 'Competition'
            ));

        foreach ($mediaList as $media){
                if($media->getType() == 'image'){
                       $coverImage= $media;
                        continue;
                }

                if($media->getType() == 'video'){
                       $coverVideo= $media;
                        continue;
                }
        }

        $form = $this->createForm(CompetitionType::class, $competition, array('translation_domain' => $this->translationDomain,
                'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $competition->setQuestionsCount(count($competition->getQuestions()));
                $competition->setQuestionsCountEn(count($competition->getQuestionsEn()));
                $dm->persist($competition);
                $this->slugifier($competition);

                $dm->flush();



                if (count($mediaList) > 0) {

                    $firstImg = $mediaList[0];

                    $this->oldDir = $firstImg->getUploadRootDir();
                    $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $competition->getId();
                    if (!file_exists($newDir)) {
                        @mkdir($newDir);
                    }
                }
                foreach ($mediaList as $image) {
                    $oldFilePath = $this->oldDir . "/" . $image->getPath();
                    $newFilePath = $newDir . "/" . $image->getPath();
                    @rename($oldFilePath, $newFilePath);
                }
                if ($competition->getCoverType() == "image" && isset($coverImage)) {
                    $coverImage->setCompetition($competition);
                    $competition->setCover($coverImage);
                    if (isset($coverVideo))
                        $dm->remove($coverVideo);
                }

                if ($competition->getCoverType() == "video" && isset($coverVideo)) {
                    $coverVideo->setCompetition($competition);
                    $competition->setCover($coverVideo);
                    if (isset($coverImage))
                        $dm->remove($coverImage);
                }


                if ($competition->getCoverType() == "none") {
                    if (isset($coverVideo))
                        $dm->remove($coverVideo);
                    if (isset($coverImage))
                        $dm->remove($coverImage);
                }


                $dm->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));
                return new JsonResponse(array('status' => 'reload-page'));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Competition:create.html.twig', array(
                    'form' => $form->createView(),
                    'coverImage' => $coverImage,
                    'coverVideo' => $coverVideo,
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

        $dm = $this->get('doctrine_mongodb')->getManager();
        $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($id);

            if (!$competition){
                throw $this->createNotFoundException($this->trans('Wrong id'));
            }

        $form = $this->createForm(CompetitionType::class, $competition, array('translation_domain' => $this->translationDomain,
            'isNew' => $competition->getStatus() == "new"?true:false,
                'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')));

        $coverImage = NULL;
        $coverVideo = NULL;

        $mediaList = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
            'competition' => $competition->getId(),
            'collectionType' => 'Competition'
        ));

        foreach ($mediaList as $media){
                if($media->getType() == 'image'){
                       $coverImage= $media;
                        continue;
                }

                if($media->getType() == 'video'){
                       $coverVideo= $media;
                        continue;
                }
        }

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->flush();

                if ($competition->getCoverType() == "image" && isset($coverImage)) {
                    $coverImage->setCompetition($competition);
                    $competition->setCover($coverImage);
                    if (isset($coverVideo))
                        $dm->remove($coverVideo);
                }

                if ($competition->getCoverType() == "video" && isset($coverVideo)) {
                    $coverVideo->setCompetition($competition);
                    $competition->setCover($coverVideo);
                    if (isset($coverImage))
                        $dm->remove($coverImage);
                }


                if ($competition->getCoverType() == "none") {
                    if (isset($coverVideo))
                        $dm->remove($coverVideo);
                    if (isset($coverImage))
                        $dm->remove($coverImage);
                }
                $dm->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));
                return new JsonResponse(array('status' => 'reload-page'));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Competition:edit.html.twig', array(
                    'form' => $form->createView(),
                    'coverImage' => $coverImage,
                    'coverVideo' => $coverVideo,
                    'title' => $this->trans('Edit Competition', array(), $this->translationDomain),
                    'form_theme' => 'IbtikarGlanceDashboardBundle:Competition:form_theme_competition.html.twig',
                    'translationDomain' => $this->translationDomain,
                    'room' => $this->calledClassName
        ));
    }

    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array(),
            "createdAt" => array("type" => "date"),
            "questionsCount" => array(),
            "slug" => array('type' => 'slug'),
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

    public function getDocumentCount()
    {

       return $this->getTabCount();
    }





    public function unpublishAction(Request $request) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_UNPUBLISH') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'),'count'=>  $this->getDocumentCount());
            return new JsonResponse($result);
        }
        $id = $request->get('id');
        if (!$id) {
            return $this->getFailedResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository($this->getObjectShortName())->find($id);

        if (!$document || $document->getDeleted() || $document->getStatus()==  Competition::$statuses['unpublish']) {
            return new JsonResponse(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation'),'count'=>  $this->getDocumentCount()));

        }
        try {
            $document->setStatus(Competition::$statuses['unpublish']);
            $document->setUnpublishedAt(new \DateTime());
            $document->setUnPublishedBy($this->getUser());
            $dm->flush();
        } catch (\Exception $e) {

            return $this->getFailedResponse();
        }

        $count = $this->getDocumentCount();

        return new JsonResponse(array('status' => 'success', 'message' => $this->get('translator')->trans('done sucessfully'),'count'=>$count));
    }

    public function updateAnswerStatusAction(Request $request) {
        $securityContext = $this->get('security.authorization_checker');
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        if (!$securityContext->isGranted('ROLE_' . strtoupper($this->calledClassName) . '_STOPRESUME') && !$securityContext->isGranted('ROLE_ADMIN')) {
            $result = array('status' => 'reload-table', 'message' => $this->trans('You are not authorized to do this action any more'),'count'=>  $this->getDocumentCount());
            return new JsonResponse($result);
        }
        $id = $request->get('id');
        $answerEnable = $request->get('status');

        if (!$id || !$answerEnable) {
            return $this->getFailedResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $document = $dm->getRepository($this->getObjectShortName())->find($id);
        switch ($answerEnable) {
            case 'enabled':
                $answeToBeUpdate = true;
                break;
            case 'disabled':
                $answeToBeUpdate = FALSE;
                break;
        }

        if (!$document || $document->getDeleted() || $document->getAnswersEnabled() == $answeToBeUpdate) {
            return new JsonResponse(array('status' => 'failed', 'message' => $this->get('translator')->trans('failed operation'), 'count' => $this->getDocumentCount()));
        }
        $document->setAnswersEnabled($answeToBeUpdate);
        $dm->flush();
        $count = $this->getDocumentCount();

        return new JsonResponse(array('status' => 'success', 'message' => $this->get('translator')->trans('done sucessfully'),'count'=>$count));
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

            if ($competition->getExpiryDate() && $competition->getExpiryDate() < new \DateTime()) {
                $result = array('status' => 'reload-table', 'message' => $this->trans('Date expired',array(),  $this->translationDomain));
                return new JsonResponse($result);
            }

            return $this->render('IbtikarGlanceDashboardBundle::publishModal.html.twig', array(
                    'goodyStar' => $competition->getGoodyStar(),
                    'displayGoodyStar' => TRUE,
                    'translationDomain' => $this->translationDomain,
                    'locations' => array(),
                    'currentLocations' => array(),
                    'document' => $competition
            ));
        } else if ($request->getMethod() === 'POST') {

            $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->findOneById($request->get('documentId'));
            if (!$competition) {

                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }


            $competittionStatus = $competition->getStatus();
            $status = $request->get('status');
            $goodyStar = $request->get('goodyStar');
            if ($status != $competittionStatus) {
                $result = array('status' => 'reload-table', 'message' => $this->trans('not done'));
                return new JsonResponse($result);
            }


            switch ($competittionStatus) {
                case 'new':
                    $competition->setStatus(Competition::$statuses['publish']);
                    $competition->setPublishedBy($this->getUser());
                    $competition->setPublishedAt(new \DateTime());
                    if($goodyStar){
                    $competition->setGoodyStar($goodyStar);
                    }
                    break;
                case 'unpublish':
                    $newCompetition = clone $competition;
                    $dm->persist($newCompetition);
                    $this->slugifier($newCompetition);
                    $dm->flush();

                    $images = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array(
                        'competition' => $competition->getId(),
                        'collectionType' => 'Competition'
                    ));

                    if (count($images) > 0) {
                        $newCoverPhoto = clone $images[0];
                        $dm->persist($newCoverPhoto);
                        $newCoverPhoto->setCompetition($newCompetition);
                        if ($images[0]->getType() == 'image') {
                            $this->oldDir = $images[0]->getUploadRootDir();

                            $newDir = substr($this->oldDir, 0, strrpos($this->oldDir, "/")) . "/" . $newCompetition->getId();
                            if (!file_exists($newDir)) {
                                @mkdir($newDir);
                            }
                            $oldFilePath = $this->oldDir . "/" . $images[0]->getPath();
                            $newFilePath = $newDir . "/" . $images[0]->getPath();

                            copy($oldFilePath, $newFilePath);
                        }
                    }
                    $newCompetition->setStatus(Competition::$statuses['publish']);
                    $newCompetition->setPublishedBy($this->getUser());
                    $newCompetition->setGoodyStar($goodyStar);
                    $newCompetition->setPublishedAt(new \DateTime());
                    break;
            }
            $dm->flush();

            $publishResult = array('status' => 'success','message'=>$this->trans('done sucessfully'));
            return new JsonResponse(array_merge($publishResult, $this->getTabCount()));
        }
    }

    public function getListJsonData($request,$renderingParams)
    {
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
                        $oneDocument['actions'] = $this->renderView('IbtikarGlanceDashboardBundle:Competition:_listActions.html.twig', $templateVars);
                        continue;
                    }
                }
                $getfunction = "get" . ucfirst($value);
                if ($value == 'name' && $document instanceof \Ibtikar\GlanceUMSBundle\Document\Role) {
                    $oneDocument[$value] = '<a class="dev-role-getPermision" href="javascript:void(0)" data-id="' . $document->getId() . '">' . $document->$getfunction() . '</a>';
                }
                elseif ($value == 'username') {
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
                    if ($document->$getfunction()) {
                        $oneDocument[$value] = $this->trans('answer ' . strtolower($document->$getfunction()), array(), $this->translationDomain);
                    } else {
                        $oneDocument[$value] = $this->trans('answer false', array(), $this->translationDomain);
                    }
                } elseif ($value == 'email' && !method_exists($document, 'get' . ucfirst($value))) {
                    $oneDocument[$value] = $this->get('app.twig.property_accessor')->propertyAccess($document, 'createdBy', $value);
                } elseif ($value == 'status') {
                    $oneDocument[$value] = $this->trans($document->$getfunction(), array(), $this->translationDomain);
                } elseif ($value == 'slug') {
                    $request->setLocale('ar');
                    $oneDocument[$value] = '<a href="' . $this->generateUrl('ibtikar_goody_competition_view_ar', array('slug' => $document->getSlug()), UrlGeneratorInterface::ABSOLUTE_URL) . '" target="_blank">' . $this->generateUrl('ibtikar_goody_competition_view_ar', array('slug' => $document->$getfunction()), UrlGeneratorInterface::ABSOLUTE_URL) . ' </a>';
                } elseif ($value == 'profilePhoto' || $value == 'coverPhoto') {
                    $image = $document->$getfunction();
                    if (!$image) {
                        $image = 'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg';
                    } else {
                        $image = $image->getWebPath();
                    }
                    $oneDocument[$value] = '<div class="thumbnail small-thumbnail"><div class="thumb thumb-slide"><img alt="" src="/' . $image . '">
                            <div class="caption"><span> <a data-popup="lightbox" class="btn btn-primary btn-icon" href="/' . $image . '"><i class="icon-zoomin3"></i></a>
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
        $rowsHeader=$this->getColumnHeaderAndSort($request);
        return new JsonResponse(array('status' => 'success','data' => $documentObjects, "draw" => 0, 'sEcho' => 0,'columns'=>$rowsHeader['columnHeader'],
            "recordsTotal" => $renderingParams['total'],
            "recordsFiltered" => $renderingParams['total']));
    }

    public function viewAction(Request $request,$id)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->find($id);
        if (!$competition){
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $questions = $competition->getQuestions();
        $drawChart=array();
        $drawChartColor=array();
        foreach ($questions as $index => $question) {
            switch ($question->getQuestionType()) {
                case "multiple answer":
                case "single answer":
                $answerPieChart=array();
                $answerPieChartColor=array();
               foreach ($question->getAnswers() as $index => $answer) {
                        $key = $index + 1;
                        $answerPieChartColor[]= Competition::$COMPETITION_ANSWER_Highlighted_COLORS["color$key"];

                        $answerPieChart[] = array($answer->getAnswer(),$answer->getPercentage(),
//                            "color" => Competition::$COMPETITION_ANSWER_Highlighted_COLORS["color$key"],
//                            "label" => $this->trans("answer $key")
                            );
                    }
                    $drawChart[$question->getId()]=  json_encode($answerPieChart);
                    $drawChartColor[$question->getId()]=json_encode($answerPieChartColor);


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

                default:
                    break;
            }
        }
        $questions = $competition->getQuestionsEn();
        $drawChartEn=array();
        $drawChartEnColor=array();
        foreach ($questions as $index => $question) {
            switch ($question->getQuestionType()) {
                case "multiple answer":
                case "single answer":
                $answerPieChart=array();
                $answerPieChartColor=array();
               foreach ($question->getAnswers() as $index => $answer) {
                        $key = $index + 1;
                        $answerPieChartColor[] = Competition::$COMPETITION_ANSWER_Highlighted_COLORS["color$key"];

                        $answerPieChart[] = array($answer->getAnswer(),$answer->getPercentage()
                            );
                    }
                    $drawChartEn[$question->getId()]=  json_encode($answerPieChart);
                    $drawChartEnColor[$question->getId()]=  json_encode($answerPieChartColor);


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

                default:
                    break;
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Competition:view.html.twig', array(
                'translationDomain' => $this->translationDomain,
                'competition' => $competition,
                'competition' => $competition,
                'drawChart' => array_values($drawChart),
                'drawChartEn' => array_values($drawChartEn),
                'drawChartColor' => array_values($drawChartColor),
                'drawChartEnColor' => array_values($drawChartEnColor)
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

    /**
     * Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function competitionStatisticsAction(Request $request) {
        $requestedDates = $this->getRequiredFromToDatesOrInvalidResponseFromCurrentRequest($request);
        if ($requestedDates instanceof JsonResponse) {
            return $requestedDates;
        }
        $responseData = array('status' => 'success', 'code' => 200, 'counts' => array());
        $dm = $this->get('doctrine_mongodb')->getManager();
        $competitionRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition');
        $responseData['counts']['publishedCount'] = $competitionRepo->createQueryBuilder()
                        ->field('publishedAt')->gte($requestedDates['from'])
                        ->field('publishedAt')->lte($requestedDates['to'])
                        ->field('status')->equals(Competition::$statuses['publish'])
                        ->getQuery()->count();
        $responseData['counts']['newCount'] = $competitionRepo->createQueryBuilder()
                        ->field('createdAt')->gte($requestedDates['from'])
                        ->field('createdAt')->lte($requestedDates['to'])
                        ->getQuery()->count();
        $responseData['counts']['answersCount'] = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:QuestionAnswer')
                        ->field('createdAt')->gte($requestedDates['from'])
                        ->field('createdAt')->lte($requestedDates['to'])
                        ->getQuery()->count();
        return new JsonResponse($responseData);
    }


    public function slugifier($competition)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($competition->getTitle(), 100));
        $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($competition->getTitleEn(), 100));

        $arabicCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
                ->field('deleted')->equals(FALSE)
                ->field('slug')->equals($slugAr)
                ->field('id')->notEqual($competition->getId())->
                getQuery()->execute()->count();

        $englishCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
                ->field('deleted')->equals(FALSE)
                ->field('slugEn')->equals($slugEn)
                ->field('id')->notEqual($competition->getId())->
                getQuery()->execute()->count();
        if ($arabicCount != 0) {
            $slugAr = ArabicMongoRegex::slugify($this->getShortDescriptionStringAr($competition->getTitle(), 100) . "-" . date('ymdHis'));
        }
        if ($englishCount != 0) {
            $slugEn = ArabicMongoRegex::slugify($this->getShortDescriptionStringEn($competition->getTitleEn(), 100) . "-" . date('ymdHis'));
        }
        $competition->setSlug($slugAr);
        $competition->setSlugEn($slugEn);


        $dm->flush();
    }
}
