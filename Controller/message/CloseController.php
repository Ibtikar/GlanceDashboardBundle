<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\message;

use Ibtikar\GlanceDashboardBundle\Controller\MessageController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Message;

class CloseController extends MessageController
{

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'message' . strtolower($calledClassName[1]);
        $this->messageStatus = Message::$statuses['close'];
    }


    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setDefaultSortBy("updatedAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }

}
