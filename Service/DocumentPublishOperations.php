<?php

namespace Ibtikar\GlanceDashboardBundle\Service;
use Ibtikar\GlanceDashboardBundle\Service\PublishOperations;


class DocumentPublishOperations extends PublishOperations
{

    static $TIME_OUT = "time out";
    static $ASSIGN_TO_OTHER_USER = "assign to other user";
    static $ASSIGN_TO_ME = "assign to me";
    protected $container;
    protected $dm;

      public function __construct($container) {
        parent::__construct($container->get('security.token_storage'), $container->get('doctrine_mongodb'), $container->get('redirect'), $container->get('router'),$container->get('translator'));
        $this->container = $container;
    }

    public function setType(\Ibtikar\GlanceDashboardBundle\Document\Publishable $document)
    {

    }

    public function validateDelete($recipe) {
        $translator = $this->container->get('translator');
        //invalid material OR user who wants to forward is not the material owner
        if ($recipe->getStatus() == 'deleted') {
            if ($recipe->getType() == 'recipe') {
                return array("status" => "error", "message" => $this->translator->trans('already deleted', array(), 'recipe'));
            } elseif ($recipe->getType() == 'article') {
                return array("status" => "error", "message" => $this->translator->trans('article already deleted', array(), 'recipe'));
            } else {
                return array("status" => "error", "message" => $this->translator->trans('tip already deleted', array(), 'recipe'));
            }
        }
        return array("status"=>'success',"message" => $this->translator->trans('done sucessfully'));
    }
}
