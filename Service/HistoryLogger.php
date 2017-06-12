<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Ibtikar\GlanceDashboardBundle\Document\History;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class HistoryLogger {
    /* @var $dm DocumentManager */

    private $dm;
    private $context;

    public function __construct(ManagerRegistry $mr, $context) {
        $this->dm = $mr->getManager();
        $this->context = $context;
    }

    private function getUser() {
        if ($this->context->getToken()) {
            return $this->context->getToken()->getUser();
        } else {
            return null;
        }
    }

    public function log($document, $action, $message = null,$related=null) {
        $log = new History();
        $log->setDocument($document);
        $log->setAction($action);
        $log->setMessage($message);
        if($related){
            $log->setRecipe($related);
        }
        $this->dm->persist($log);
        $this->dm->flush();
        return $log;
    }

}
