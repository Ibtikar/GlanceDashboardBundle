<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\stars;

use Ibtikar\GlanceDashboardBundle\Controller\StarsController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Stars;

class RejectedController extends StarsController
{

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'stars' . strtolower($calledClassName[1]);
        $this->starsStatus = Stars::$statuses['rejected'];
    }

    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setActions(array('Approve', 'ViewOne'));
        $queryBuilder = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Stars')
                        ->field('status')->equals($this->starsStatus)
                        ->field('deleted')->equals(false);
        if (isset($queryBuilder))
            $this->listViewOptions->setListQueryBuilder($queryBuilder);
    }
}
