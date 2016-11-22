<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

class RecipeOperations
{

    static $TIME_OUT = "time out";
    static $ASSIGN_TO_OTHER_USER = "assign to other user";
    static $ASSIGN_TO_ME = "assign to me";
    protected $container;
    protected $dm;

    public function __construct($container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
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
}
