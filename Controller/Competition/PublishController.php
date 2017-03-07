<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Competition;

use Ibtikar\GlanceDashboardBundle\Controller\CompetitionController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Competition;

class PublishController extends CompetitionController
{
    protected $status = 'publish';

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'competition' . strtolower($calledClassName[1]);
        $this->recipeStatus = Competition::$statuses['publish'];
    }


    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array(),
            "createdAt" => array("type" => "date"),
            "questionsCount" => array(),
            "expiryDate" => array("type" => "date"),
            'noOfAnswer' => array(),
            'answersEnabled' => array(),
        );
        $this->defaultListColumns = array(
            "title",
            "createdAt",
            "questionsCount",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }


    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setActions(array('Edit', 'ViewOne','Delete','unPublish','StopResume'));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }
    public function listPublishCompetitionAction(Request $competition)
    {

        $this->listStatus = 'list_publish_competition';
        $this->listName = 'competition' . $this->status . '_' . $this->listStatus;
        return parent::listAction($competition);
    }

    public function changeListPublishCompetitionColumnsAction(Request $competition)
    {
        $this->listStatus = 'list_publish_competition';
        $this->listName = 'competition' . $this->status . '_' . $this->listStatus;
        return parent::changeListColumnsAction($competition);
    }



}
