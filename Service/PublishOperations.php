<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceDashboardBundle\Document\PublishLocation;
use Ibtikar\GlanceDashboardBundle\Document\Publishable;
use Ibtikar\GlanceUMSBundle\Document\Staff;
use Ibtikar\AppBundle\Service\Redirect;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
abstract class PublishOperations {

    /** @var DocumentManager $dm */
    protected $dm;

    /** @var SecurityContextInterface $securityContext */
    protected $securityContext;

    /** @var Redirect $redirect */
    protected $redirect;

    /** @var UrlGeneratorInterface $router */
    protected $router;

    protected $type;

    /**
     * @param SecurityContextInterface $securityContext
     * @param ManagerRegistry $mr
     * @param Redirect $redirect
     * @param UrlGeneratorInterface $router
     */
    public function __construct(SecurityContextInterface $securityContext, ManagerRegistry $mr, Redirect $redirect, UrlGeneratorInterface $router) {
        $this->dm = $mr->getManager();
        $this->securityContext = $securityContext;
        $this->redirect = $redirect;
        $this->router = $router;
    }

    abstract function setType(Publishable $document);

    /**
     * @return User|null
     */
    public function getUser() {
        $token = $this->securityContext->getToken();
        if ($token) {
            $user = $token->getUser();
            if (is_object($user) && $user instanceof Staff) {
                return $user;
            }
        }
        return null;
    }

    /**
     * @return User|null
     */
    public function getLoggedInUser() {
        $token = $this->securityContext->getToken();
        if ($token) {
            $user = $token->getUser();
            if (is_object($user) && $user instanceof User) {
                return $user;
            }
        }
        return null;
    }

    /**
     * @param Publishable $document
     * @return string the path relative to the domain (absolute path)
     */
    public function getFrontEndUrl(Publishable $document) {
        // only routes used in onKernelRequest function in Redirect listener is allowed
        return $this->router->generate('app_view', array('slug' => $document->getSlug()));
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Publishable $document
     */
    public function hideFrontEndUrl(Publishable $document) {
        $slug = $this->dm->getRepository('IbtikarAppBundle:Slug')->findOneBy(array('referenceId' => $document->getId()));
        if($slug) {
            $slug->setPublish(false);
            $this->dm->flush($slug);
        }
        $this->redirect->addTemporaryRedirect($this->getFrontEndUrl($document), null, TRUE);
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Publishable $document
     */
    public function showFrontEndUrl(Publishable $document) {
        $slug = $this->dm->getRepository('IbtikarAppBundle:Slug')->findOneBy(array('referenceId' => $document->getId()));
        if($slug) {
            $slug->setPublish(true);
            $this->dm->flush($slug);
        }
        $this->redirect->removeRedirect($this->getFrontEndUrl($document));
    }



    /**
     * @param Publishable $document
     * @param array $locations
     * @param boolean $rePublish
     */
    public function publish(Publishable $document, array $locations, $rePublish = false) {

        if (php_sapi_name() !== 'cli') {
        $error = $this->validateToPublish($document,$locations);

        if($error){
            return $error;
        }
        }

        $currentLocations = array();
        foreach ($this->getAllowedLocations($document) as $location) {
            $currentLocations[] = $location->getId();
        }


        foreach ($locations as $slocation) {
            if(!in_array($slocation->getId(), $currentLocations))
                return array("status"=>"error", "message"=>"wronge locations");
        }

        if (php_sapi_name() !== 'cli') {
        // merge selected locations by user with the default publishing locations
        $locations = array_merge($locations, $this->getAllowedLocations($document, false)->toArray());
        }
        $user = null;
        if (php_sapi_name() === 'cli') {
            $user = $document->getPublishedBy();
        } else {
            $user = $this->getUser();
        }

        foreach ($locations as $location) {
            if(!$rePublish || $location->getIsSelectable()) {
            $this->publishInLocation($document, $location->getPublishedLocationObject($user),$location->getMaxNumberOfMaterials());
            }
        }
        if (!$rePublish) {
            $document->setPublishedAt(new \DateTime());
        }
        $document->setPublishedBy($user);

        if (!$rePublish) {
        // comments setting
        if ($document->getCommentsEnabled()) {
            $commentsDuration = $document->getCommentsDuration();
            // if we do not have a duration then the comics was unpublished then published we do not need to do anything
            if($commentsDuration) {
                if ($commentsDuration === 'Unlimited') {
                    $document->setCommentsExpiryDate(null);
                    $document->setCommentsWillNeverExpire(true);
                } else {
                    $commentsExpiryDate = clone $document->getPublishedAt();
                    $commentsExpiryDate->modify("+1 $commentsDuration");
                    $document->setCommentsExpiryDate($commentsExpiryDate);
                    $document->setCommentsWillNeverExpire(false);
                }
            }
        } else {
            // comments set to not allowed, clear the comments settings
            $document->setCommentsExpiryDate(null);
            $document->setCommentsWillNeverExpire(false);
        }
        $document->setCommentsDuration(null);
        $authors = $document->getAuthor();
            if ($document instanceof \Ibtikar\AppBundle\Document\Material) {
                foreach ($authors as $author) {
                    $author->setTotalDocumentsLikesCount($author->getTotalDocumentsLikesCount() + $document->getNoOfLikes());
                    $author->setTotalDocumentsCommentsCount($author->getTotalDocumentsCommentsCount() + $document->getNoOfComments());
                    $author->setTotalDocumentsViewsCount($author->getTotalDocumentsViewsCount() + $document->getNoOfViews());
                }
        } else if($document instanceof \Ibtikar\AppBundle\Document\Comics) {
           if($authors)
           {
            $authors->setTotalDocumentsLikesCount($authors->getTotalDocumentsLikesCount() + $document->getNoOfLikes());
            $authors->setTotalDocumentsCommentsCount($authors->getTotalDocumentsCommentsCount() + $document->getNoOfComments());
            $authors->setTotalDocumentsViewsCount($authors->getTotalDocumentsViewsCount() + $document->getNoOfViews());
           }
        }

        $this->calculateProcessingTime($document);
        $this->showFrontEndUrl($document);
        }
        if (php_sapi_name() !== 'cli') {
            if ($document instanceof \Ibtikar\AppBundle\Document\Material && $document->getStatus() == 'autopublish') {
//                $documentPublishLocations = $document->getPublishLocations();
//                foreach ($documentPublishLocations as $documentPublishLocation) {
//                    $documentPublishLocation->setPublishedAt(new \DateTime());
//                }
                $document->setAutoPublishDate(null);
            }
        }

        $this->dm->flush();

    }

    /**
     * @param Publishable $document
     */
    public function unpublish(Publishable $document) {
        foreach ($document->getPublishLocations() as $location) {
            $this->unpublishFromLocation($document, $location, false);
        }
        $document->setUnpublishedAt(new \DateTime());
    }

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param Publishable $document
     * @param PublishLocation $location
     */
    public function addPublishLocation(Publishable $document, PublishLocation $location) {
        $this->setType($document);
        $document->addPublishLocation($location);
    }

    /**
     * @param Publishable $document
     * @param PublishLocation $location
     */
    public function publishInLocation(Publishable $document, PublishLocation $location,$maxNumberInLocation = 0) {
        $this->setType($document);
        if($maxNumberInLocation > 0){
            $this->readyLocationVacancy($location,$maxNumberInLocation);
        }
        if (php_sapi_name() !== 'cli') {
        $document->addPublishLocation($location);
        }
        if ($location->getSection() == 'Home-BreakingNews' && $document->getBreakingNewColor() =='red') {
            $message = new PushNotificationMessage();
            $message->setMaterial($document);
            $message->setAndroidStatus(PushNotificationMessage::$status['new']);
            $message->setIosStatus(PushNotificationMessage::$status['new']);
            $message->setPublishedAt(new \DateTime());
            $message->setType(PushNotificationMessage::$types['breaking-news']);
            $this->dm->persist($message);
        }
        if ($location->getSection() == 'Home-ImportantNews') {
            $document->setLightVersionDate($location->getPublishedAt());
        }
    }

    /**
     * @param Publishable $document
     * @param PublishLocation $location
     * @param boolean $callUnpublish
     */
    public function unpublishFromLocation(Publishable $document, PublishLocation $location, $callUnpublish = true,$removefromLightVersion=true) {
        $document->removePublishLocation($location);
        if ($location->getSection() == 'Home-ImportantNews' && $removefromLightVersion) {
            $document->setLightVersionDate(NULL);
        }
        if ($callUnpublish && count($document->getPublishLocations()) === 0) {
            $this->unpublish($document);
        }
    }




/**
 * @author Ola <ola.ali@ibtikar.net.sa>
 * @param Publishable $document
 * @param type $selectables
 * @return type
 * @throws \Exception
 */

    public function getAllowedLocations(Publishable $document, $selectables = true){


        $result = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Location')
                        ->field('type.'.$document->getType())->exists(true);

        if($selectables){
            $result->field('isSelectable')->equals($selectables);
        }else{
            $result->field('isSelectable')->notEqual(true);
        }

//        if(is_null($document->getDefaultCoverPhoto())){
//            $result->field('requiredCoverImage')->notEqual(true);
//        }

        $result = $result->getQuery()->execute();

        if($result->count() == 0){
            throw new \Exception("Empty Location List");
        }

        return $result;
    }





    /**
     * manage published locations of any publishable document
     * @param \Ibtikar\AppBundle\Document\Publishable $document
     * @param $locations array of Location objects
     * @return type
     */
    public function managePublishControl(Publishable $document, $locations) {

        $newLocationsSections = array();
        foreach ($locations as $location) {
            $newLocationsSections[] = $location->getSection();
        }
        $oldPublishLocations = $document->getPublishLocations();
        $oldLocationsSections = array();
        foreach($oldPublishLocations as $publishLocation) {
            $locationInfo = $this->dm->getRepository('IbtikarAppBundle:Location')->findBy(array('section' => $publishLocation->getSection()));
            $location = $locationInfo[0];

            //this condition to ignore the locations that's selected by default
            if ($location->getIsSelectable()) {
                $oldLocationsSections[] = $publishLocation->getSection();
            }
        }

        $removedLocationsSections = array_diff($oldLocationsSections, $newLocationsSections);
        $adddedLocationsSections = array_diff($newLocationsSections,$oldLocationsSections);

        foreach ($removedLocationsSections as $section) {
            $targetLocationObject;
            foreach ($oldPublishLocations as $location) {
                if($location->getSection() == $section)
                    $targetLocationObject = $location;
            }
            $this->unpublishFromLocation($document, $targetLocationObject);
        }

        foreach ($adddedLocationsSections as $section) {

            $locationInfo = $this->dm->getRepository('IbtikarAppBundle:Location')->findBy(array('section' => $section));

            $location = $locationInfo[0];

            $publishLocation = $location->getPublishedLocationObject($this->getUser());

            $this->publishInLocation($document, $publishLocation,$location->getMaxNumberOfMaterials());
        }

        $this->dm->flush();

        return array("status"=>'success',"message"=>'true');
    }


    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param \Ibtikar\AppBundle\Document\Publishable $document
     * @param $locations array of Location objects
     * @return type
     */
    public function manageAutoPublishControl(Publishable $document, $locations,\DateTime $autoPublishDate = null) {

        if (!($autoPublishDate instanceof \DateTime) || $autoPublishDate < new \DateTime()) {
            return array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a publish date after today'));
        }

        $newLocationsSections = array();
        foreach ($locations as $location) {
            $newLocationsSections[] = $location->getSection();
        }
        $oldPublishLocations = $document->getPublishLocations();
        $oldLocationsSections = array();
        foreach($oldPublishLocations as $publishLocation) {
            $locationInfo = $this->dm->getRepository('IbtikarAppBundle:Location')->findBy(array('section' => $publishLocation->getSection()));
            $location = $locationInfo[0];

            //this condition to ignore the locations that's selected by default
            if ($location->getIsSelectable()) {
                $oldLocationsSections[] = $publishLocation->getSection();
            }
        }

        $removedLocationsSections = array_diff($oldLocationsSections, $newLocationsSections);
        $adddedLocationsSections = array_diff($newLocationsSections,$oldLocationsSections);

        foreach ($removedLocationsSections as $section) {
            $targetLocationObject;
            foreach ($oldPublishLocations as $location) {
                if($location->getSection() == $section)
                    $targetLocationObject = $location;
            }
            $this->unpublishFromLocation($document, $targetLocationObject,false);
        }

        foreach ($adddedLocationsSections as $section) {

            $locationInfo = $this->dm->getRepository('IbtikarAppBundle:Location')->findBy(array('section' => $section));

            $location = $locationInfo[0];

            $this->addPublishLocation($document, $location->getPublishedLocationObject($this->getUser(),$autoPublishDate));
        }

        $document->setAutoPublishDate($autoPublishDate);

        $this->dm->flush();

        return array("status"=>'success',"message"=>'true');
    }

}
