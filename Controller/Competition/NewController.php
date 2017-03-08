<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Competition;

use Ibtikar\GlanceDashboardBundle\Controller\CompetitionController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Competition;

class NewController extends CompetitionController
{

    protected $status = 'new';

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'competition' . strtolower($calledClassName[1]);
        $this->recipeStatus = Competition::$statuses['new'];
    }

    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne'));
        $this->listViewOptions->setBulkActions(array("Delete"));

        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }

    public function listNewCompetitionAction(Request $competition)
    {

        $this->listStatus = 'list_new_competition';
        $this->listName = 'competition' . $this->status . '_' . $this->listStatus;
        return parent::listAction($competition);
    }

    public function changeListNewCompetitionColumnsAction(Request $competition)
    {
        $this->listStatus = 'list_new_competition';
        $this->listName = 'competition' . $this->status . '_' . $this->listStatus;
        return parent::changeListColumnsAction($competition);
    }
}
