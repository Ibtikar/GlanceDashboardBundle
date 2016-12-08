<?php

namespace Ibtikar\GlanceDashboardBundle\Service;
use Ibtikar\GlanceDashboardBundle\Service\PublishOperations;


class RecipeOperations extends PublishOperations
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

    /**
     *
     */
    public function assignToMe($recipe, $status)
    {
        $recipe = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('id')->equals($recipe)
                ->field('deleted')->equals(false)
                ->getQuery()->getSingleResult();
        $token = $this->container->get('security.token_storage')->getToken();
        if (!$recipe) {
            return self::$TIME_OUT;
        }
        if ($recipe->getAssignedTo() != NULL) {
            if ($recipe->getAssignedTo()->getId() === $token->getUser()->getId() || $recipe->getStatus() != $status) {
                return self::$TIME_OUT;
            } else {
                return self::$ASSIGN_TO_OTHER_USER;
            }
        }

//        $log = $this->container->get('history_logger')->log($recipe, History::$ASSIGN, $recipe->getRoom());

        $this->assignRecipeToUser($recipe, $token->getUser());
//        $this->container->get('notify_user')->notifyUser($log);
        return self::$ASSIGN_TO_ME;
    }

    public function assignRecipeToUser($recipe, $user)
    {
        $recipe->setAssignedTo($user);
        $this->dm->flush();
    }

    public function setType(\Ibtikar\GlanceDashboardBundle\Document\Publishable $document)
    {

    }

    public function validateDelete($recipe) {
        $translator = $this->container->get('translator');
        //invalid material OR user who wants to forward is not the material owner
        if ($recipe->getStatus()=='deleted') {
            return array("status"=>"error","message"=>$this->translator->trans('already deleted',array(),'recipe'));
        }
        return array("status"=>'success',"message" => $this->translator->trans('done sucessfully'));
    }
}