<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Competition;

use Ibtikar\GlanceDashboardBundle\Controller\CompetitionController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\CompetitionAnswer;

class CompetitionAnswerController extends CompetitionController
{

    protected $translationDomain = 'competition';

    public function __construct()
    {
        parent::__construct();
        $this->calledClassName = 'competitionanswer';
    }

    protected function configureListColumns()
    {
        $this->allListColumns = array(
            "fullName" => array(),
            "email" => array(),
//            "description" => array(),
            "phone" => array('type'=>'phone','class'=>'phoneNumberLtr'),
//            "profilePhoto" => array("type" => "refereceImage", 'isSortable' => FALSE),
            "createdAt" => array("type"=>"date"),
//            "updatedAt"=> array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "fullName",
            "email",
            "phone",
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

        $queryBuilder = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder("IbtikarGlanceDashboardBundle:CompetitionAnswer")->field('competition')->equals(new \MongoId($id))
                ->field('deleted')->equals(false);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array('ViewAnswerOne'));

        $this->listViewOptions->setListQueryBuilder($queryBuilder);

        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Competition:viewAnswer.html.twig");
    }

    protected function doList(Request $request)
    {
        $renderingParams = parent::doList($request);
        $id = $request->get('id');
        if (!$id) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $competition = $dm->getRepository('IbtikarGlanceDashboardBundle:Competition')->findOneById($id);
        if (!$competition) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        $renderingParams['competition'] = $competition;
        $countryArray=array();
        foreach ($competition->getCountryCount() as $country) {
          $countryArray[]=array('country'=>$country->getCountry()->getCountryName(),'frequency'=> $country->getCount()/$competition->getNoOfAnswer())  ;
        }
        $renderingParams['competitionCountry'] = json_encode($countryArray);

        return $renderingParams;
    }

    public function viewAction(Request $request, $id)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $competitionAnswer = $dm->getRepository('IbtikarGlanceDashboardBundle:CompetitionAnswer')->find($id);
        if (!$competitionAnswer) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }

        return $this->render('IbtikarGlanceDashboardBundle:Competition:viewOneAnswer.html.twig', array(
                'translationDomain' => $this->translationDomain,
                'competitionAnswer' => $competitionAnswer,

        ));
    }
}
