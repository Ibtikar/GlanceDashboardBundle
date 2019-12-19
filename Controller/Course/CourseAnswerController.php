<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Course;

use Ibtikar\GlanceDashboardBundle\Controller\CourseController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\CourseAnswer;

class CourseAnswerController extends CourseController
{

    protected $translationDomain = 'anwser';

    public function __construct()
    {
        parent::__construct();
        $this->calledClassName = 'courseanswer';
    }

    protected function configureListColumns()
    {
        $this->allListColumns = array(
            "fullName" => array(),
//            "description" => array(),
//            "profilePhoto" => array("type" => "refereceImage", 'isSortable' => FALSE),
            "createdAt" => array("type"=>"date"),
//            "updatedAt"=> array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "fullName",
            "createdAt",
//            "description",
//            "descriptionEn",
//            'createdAt',
//            "updatedAt"
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            throw $this->createNotFoundException();
        }

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder("IbtikarGlanceDashboardBundle:CourseAnswer")->field('course')->equals(new \MongoId($id))
                ->field('deleted')->equals(false);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array('ViewOneAnswer'));

        $this->listViewOptions->setListQueryBuilder($queryBuilder);

        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Course:viewAnswer.html.twig");
    }

    protected function doList(Request $request)
    {
        $renderingParams = parent::doList($request);
        $id = $request->get('id');
        if (!$id) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $route = $this->container->get('request_stack')->getCurrentRequest()->get('_route');
        if ($route == 'ibtikar_glance_dashboard_coursepublish_viewAnswers') {
            $premission = 'ROLE_COURSEPUBLISH_VIEWONEANSWER';
        } else {
            $premission = 'ROLE_COURSEUNPUBLISH_VIEWONEANSWER';
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted($premission) && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $course = $dm->getRepository('IbtikarGlanceDashboardBundle:Course')->findOneById($id);
        if (!$course) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $renderingParams['course'] = $course;
        $countryArray=array();
        foreach ($course->getCountryCount() as $country) {
          $countryArray[]=array('country'=>$country->getCountry()->getCountryName(),'frequency'=> $country->getCount()/$course->getNoOfAnswer())  ;
        }
        $renderingParams['courseCountry'] = json_encode($countryArray);

        return $renderingParams;
    }

    public function viewAction(Request $request, $id)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $courseAnswer = $dm->getRepository('IbtikarGlanceDashboardBundle:CourseAnswer')->find($id);
        if (!$courseAnswer) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $premission = 'ROLE_COURSE'.strtoupper($courseAnswer->getCourse()->getStatus()).'_VIEWONEANSWER';

        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted($premission) && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }

        return $this->render('IbtikarGlanceDashboardBundle:Course:viewOneAnswer.html.twig', array(
                'translationDomain' => $this->translationDomain,
                'courseAnswer' => $courseAnswer,

        ));
    }
}
