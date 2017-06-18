<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\stars;

use Ibtikar\GlanceDashboardBundle\Controller\StarsController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Stars;

class ApprovedController extends StarsController
{

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'stars' . strtolower($calledClassName[1]);
        $this->starsStatus = Stars::$statuses['approved'];
    }

    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array('Reject','ViewOne'));
    }

}
