<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Ibtikar\GlanceDashboardBundle\Service\PublishOperations;
use Ibtikar\GlanceDashboardBundle\Document\Publishable;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;


class RecipeOperations extends PublishOperations
{

    static $TIME_OUT = "time out";
    static $ASSIGN_TO_OTHER_USER = "assign to other user";
    static $ASSIGN_TO_ME = "assign to me";
    static $DRAFT = "draft";
    protected $container;
    protected $dm;

    public function __construct($container)
    {
        parent::__construct($container->get('security.token_storage'), $container->get('doctrine_mongodb'), $container->get('redirect'), $container->get('router'), $container->get('translator'));
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

    public function draft($recipe, $status)
    {
        $recipe = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('id')->equals($recipe)
                ->field('deleted')->equals(false)
                ->getQuery()->getSingleResult();
        if (!$recipe) {
            return self::$TIME_OUT;
        }
        if ($recipe->getStatus() != $status) {
            return self::$TIME_OUT;
        }
        $recipe->setAssignedTo(null);
        $recipe->setAutoPublishDate(null);
        $recipe->setGoodyStar(FALSE);
        foreach ($recipe->getPublishLocations() as $publishlocation) {
           $recipe->removePublishLocation($publishlocation);
        }
        $recipe->setStatus(Recipe::$statuses['draft']);
        $this->dm->flush();


        return self::$DRAFT;
    }

    public function assignRecipeToUser($recipe, $user)
    {
        $recipe->setAssignedTo($user);
        $this->dm->flush();
    }

    public function setType(\Ibtikar\GlanceDashboardBundle\Document\Publishable $document)
    {

    }

    public function publish(Publishable $document, array $locations, $rePublish = false, $goodyStar = FALSE, $migrated = FALSE)
    {
        $error = $this->validateToPublish($document, $locations, true);

        if ($error) {
            return $error;
        }
        $user = null;
        if (php_sapi_name() === 'cli') {
            $user = $document->getPublishedBy();
        } else {
            $user = $this->getUser() ? $this->getUser() : $document->getPublishedBy();
        }
        $newLocationsSections = array();
        foreach ($locations as $location) {
            $newLocationsSections[] = $location->getSection();
        }
        $oldPublishLocations = $document->getPublishLocations();
        $oldLocationsSections = array();
        foreach ($oldPublishLocations as $publishLocation) {
            $location = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findOneBy(array('section' => $publishLocation->getSection()));
            $oldLocationsSections[] = $publishLocation->getSection();
        }

        $removedLocationsSections = array_diff($oldLocationsSections, $newLocationsSections);
        $adddedLocationsSections = array_diff($newLocationsSections, $oldLocationsSections);

        foreach ($removedLocationsSections as $section) {
            $targetLocationObject;
            foreach ($oldPublishLocations as $location) {
                if ($location->getSection() == $section)
                    $targetLocationObject = $location;
            }
            $this->unpublishFromLocation($document, $targetLocationObject);
        }

        foreach ($adddedLocationsSections as $section) {

            $location = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findOneBy(array('section' => $section));
            $oldDocuments = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('status')->equals('publish')
                    ->field('publishLocations.section')->equals($location->getSection())
                    ->field('publishLocations.page')->equals($location->getPage())
                    ->getQuery()->execute();

            if (count($oldDocuments) >= $location->getMaxNumberOfMaterials()) {
                foreach ($oldDocuments as $oldestDocument) {
                    foreach ($oldestDocument->getPublishLocations() as $publishLocation) {
                        if ($publishLocation->getSection() == $location->getSection()) {
                            $this->unpublishFromLocation($oldestDocument, $publishLocation);
                        }
                    }
                }
            }
            if (php_sapi_name() !== 'cli') {
                $document->addPublishLocation($location->getPublishedLocationObject($this->getUser()));
            }
            if ($location->getSection() == 'Daily-solution') {
                $document->setDailysolutionDate(new \DateTime());
            }
        }
//        if(!$document->getMigrated()){
        $document->setPublishedAt(new \DateTime());
        $document->setPublishedBy($user);
//        }

        $document->setStatus(Recipe::$statuses['publish']);
        $document->setAssignedTo(null);
        $document->setGoodyStar($goodyStar);


        if (!$rePublish) {
            $this->showFrontEndUrl($document);
        }
//        if (php_sapi_name() !== 'cli') {
        if ($document instanceof \Ibtikar\GlanceDashboardBundle\Document\Recipe) {
            $document->setAutoPublishDate(null);
            $document->setAssignedTo(null);
        }
//        }

        $this->dm->flush();
        return array("status" => 'success', "message" => $this->translator->trans('done sucessfully'));
    }
    public function publishAutopublish(Publishable $document, array $locations, $rePublish = false, $goodyStar = FALSE, $migrated = FALSE)
    {
        $error = $this->validateToPublish($document, $locations, true);

        if ($error) {
            return $error;
        }

        $currentLocations = array();
        foreach ($this->getAllowedLocations($document) as $location) {
            $currentLocations[] = $location->getId();
        }
        foreach ($locations as $slocation) {
            if (!in_array($slocation->getId(), $currentLocations))
                return array("status" => "error", "message" => "wronge locations");
        }
            $user = $document->getPublishedBy();


        foreach ($locations as $location) {
            if ($location->getSection() == 'Daily-solution') {
                $document->setDailysolutionDate(new \DateTime());
                $oldDocuments = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                        ->field('status')->equals('publish')
                        ->field('publishLocations.section')->equals($location->getSection())
                        ->field('publishLocations.page')->equals($location->getPage())
                        ->getQuery()->execute();

                if (count($oldDocuments) >= $location->getMaxNumberOfMaterials()) {
                    foreach ($oldDocuments as $oldestDocument) {
                        foreach ($oldestDocument->getPublishLocations() as $publishLocation) {
                            if ($publishLocation->getSection() == $location->getSection()) {
                                $this->unpublishFromLocation($oldestDocument, $publishLocation);
                            }
                        }
                    }
                }
            }
        }
        $document->setPublishedAt(new \DateTime());
        $document->setPublishedBy($user);
        $document->setStatus(Recipe::$statuses['publish']);


        if (!$rePublish) {
            $this->showFrontEndUrl($document);
        }
//        if (php_sapi_name() !== 'cli') {
        if ($document instanceof \Ibtikar\GlanceDashboardBundle\Document\Recipe && $document->getStatus() == 'autopublish') {
            $document->setAutoPublishDate(null);
        }
//        }

        $this->dm->flush();
        return array("status" => 'success', "message" => $this->translator->trans('done sucessfully'));
    }

    public function autoPublish(Publishable $document, array $locations, \DateTime $autoPublishDate = null,$goodyStar=FALSE)
    {

        $error = $this->validateToPublish($document, $locations, true);

        if ($error) {
            return $error;
        }

        if (!($autoPublishDate instanceof \DateTime) || $autoPublishDate < new \DateTime()) {
            return array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a publish date after today'));
        }

        $currentLocations = array();
        foreach ($this->getAllowedLocations($document) as $location) {
            $currentLocations[] = $location->getId();
        }

        foreach ($locations as $slocation) {
            if (!in_array($slocation->getId(), $currentLocations))
                return array("status" => "error", "message" => "wronge locations");
        }

        // merge selected locations by user with the default publishing locations
//        $locations = array_merge($locations, $this->getAllowedLocations($document, false)->toArray());

        foreach ($locations as $location) {
            $this->addPublishLocation($document, $location->getPublishedLocationObject($this->getUser()));
        }

        $document->setPublishedBy($this->getUser());

        $document->setAutoPublishDate($autoPublishDate);
        $document->setStatus(Recipe::$statuses['autopublish']);
        $document->setAssignedTo(null);
        $document->setGoodyStar($goodyStar);

        $this->dm->flush();
        return array("status" => 'success', "message" => $this->translator->trans('recipe will be published at %datetime%', array('%datetime%' => $document->getAutoPublishDate()->format('Y-m-d h:i A'))));
    }

    public function validateDelete($recipe)
    {
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
        return array("status" => 'success', "message" => $this->translator->trans('done sucessfully'));
    }
}
