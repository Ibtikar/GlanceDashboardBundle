<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceDashboardBundle\Document\PublishLocation;
use Ibtikar\GlanceDashboardBundle\Document\Publishable;
use Ibtikar\GlanceUMSBundle\Document\Staff;
use Ibtikar\GlanceDashboardBundle\Service\Redirect;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Ibtikar\GlanceDashboardBundle\Document\Magazine;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
abstract class PublishOperations
{

    /** @var DocumentManager $dm */
    protected $dm;

    /** @var SecurityContextInterface $securityContext */
    protected $securityContext;

    /** @var Redirect $redirect */
    protected $redirect;
    protected $translator;

    /** @var UrlGeneratorInterface $router */
    protected $router;
    protected $type;

    /**
     * @param SecurityContextInterface $securityContext
     * @param ManagerRegistry $mr
     * @param Redirect $redirect
     * @param UrlGeneratorInterface $router
     */
    public function __construct($securityContext, ManagerRegistry $mr, Redirect $redirect, UrlGeneratorInterface $router, $translator)
    {
        $this->dm = $mr->getManager();
        $this->securityContext = $securityContext;
        $this->redirect = $redirect;
        $this->router = $router;
        $this->translator = $translator;
    }

    abstract function setType(Publishable $document);

    /**
     * @return User|null
     */
    public function getUser()
    {
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
    public function getLoggedInUser()
    {
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
    public function getFrontEndUrl(Publishable $document)
    {
        // only routes used in onKernelRequest function in Redirect listener is allowed
//        return $this->router->generate('app_view', array('slug' => $document->getSlug()));
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Publishable $document
     */
    public function hideFrontEndUrl(Publishable $document)
    {
//        $slug = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Slug')->findOneBy(array('referenceId' => $document->getId()));
//        if ($slug) {
//            $slug->setPublish(false);
//            $this->dm->flush($slug);
//        }
//        $this->redirect->addTemporaryRedirect($this->getFrontEndUrl($document), null, TRUE);
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Publishable $document
     */
    public function showFrontEndUrl(Publishable $document)
    {
//        $slug = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Slug')->findOneBy(array('referenceId' => $document->getId()));
//        if ($slug) {
//            $slug->setPublish(true);
//            $this->dm->flush($slug);
//        }
//        $this->redirect->removeRedirect($this->getFrontEndUrl($document));
    }

    /**
     * @param Publishable $document
     * @param array $locations
     * @param boolean $rePublish
     */
    public function publish(Publishable $document, array $locations, $rePublish = false,$goodyStar=FALSE)
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

//        if (php_sapi_name() !== 'cli') {
//            // merge selected locations by user with the default publishing locations
//            $locations = array_merge($locations, $this->getAllowedLocations($document, false)->toArray());
//        }
        $user = null;
        if (php_sapi_name() === 'cli') {
            $user = $document->getPublishedBy();
        } else {
            $user = $this->getUser();
        }

        foreach ($locations as $location) {
            if (!$rePublish || $location->getIsSelectable()) {
                $this->publishInLocation($document, $location->getPublishedLocationObject($user), $location->getMaxNumberOfMaterials());
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

    /**
     * @param Publishable $document
     */
    public function unpublish(Publishable $document)
    {
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
    public function addPublishLocation(Publishable $document, PublishLocation $location)
    {
        $this->setType($document);
        $document->addPublishLocation($location);
    }

    /**
     * @param Publishable $document
     * @param PublishLocation $location
     */
    public function publishInLocation(Publishable $document, PublishLocation $location, $maxNumberInLocation = 0)
    {
        $this->setType($document);
        if ($maxNumberInLocation > 0) {
            if ($document instanceof Recipe) {
                $this->type = 'Recipe';
            } elseif ($document instanceof Magazine) {
                $this->type = 'Magazine';
            }
            $this->readyLocationVacancy($location, $maxNumberInLocation);
        }
        if (php_sapi_name() !== 'cli') {
            $document->addPublishLocation($location);
        }
        if ($location->getSection() == 'Daily-solution') {
            $document->setDailysolutionDate($location->getPublishedAt());
        }
    }

    /**
     * @param Publishable $document
     * @param PublishLocation $location
     * @param boolean $callUnpublish
     */
    public function unpublishFromLocation(Publishable $document, PublishLocation $location, $callUnpublish = true, $removefromLightVersion = true)
    {
        $document->removePublishLocation($location);

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
    public function getAllowedLocations(Publishable $document, $selectables = true)
    {


        $result = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Location')
                ->field('type.' . $document->getType())->exists(true);

        if ($selectables) {
            $result->field('isSelectable')->equals($selectables);
        } else {
            $result->field('isSelectable')->notEqual(true);
        }

        if (is_null($document->getCoverPhoto())) {
            $result->field('requiredCoverImage')->notEqual(true);
        }

        $result = $result->getQuery()->execute();

//        if ($result->count() == 0) {
//            throw new \Exception("Empty Location List");
//        }

        return $result;
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
            $this->addPublishLocation($document, $location->getPublishedLocationObject($this->getUser(), $autoPublishDate));
        }

        $document->setPublishedBy($this->getUser());

        $document->setAutoPublishDate($autoPublishDate);
        $document->setStatus(Recipe::$statuses['autopublish']);
        $document->setGoodyStar($goodyStar);
        $document->setAssignedTo(null);
        $this->dm->flush();
        return array("status" => 'success', "message" => $this->translator->trans('recipe will be published at %datetime%', array('%datetime%' => $document->getAutoPublishDate()->format('Y-m-d h:i A'))));
    }

    /**
     * manage published locations of any publishable document
     * @param \Ibtikar\AppBundle\Document\Publishable $document
     * @param $locations array of Location objects
     * @return type
     */
    public function managePublishControl(Publishable $document, $locations,$goodyStar=FALSE)
    {

        $newLocationsSections = array();
        foreach ($locations as $location) {
            $newLocationsSections[] = $location->getSection();
        }
        $oldPublishLocations = $document->getPublishLocations();
        $oldLocationsSections = array();
        foreach ($oldPublishLocations as $publishLocation) {
            $locationInfo = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('section' => $publishLocation->getSection()));
            $location = $locationInfo[0];

            //this condition to ignore the locations that's selected by default
            if ($location->getIsSelectable()) {
                $oldLocationsSections[] = $publishLocation->getSection();
            }
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

            $locationInfo = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('section' => $section));

            $location = $locationInfo[0];

            $publishLocation = $location->getPublishedLocationObject($this->getUser());

            $this->publishInLocation($document, $publishLocation, $location->getMaxNumberOfMaterials());
        }
        $document->setGoodyStar($goodyStar);


        $this->dm->flush();

        return array("status" => 'success', "message" => $this->translator->trans('done sucessfully'));
    }


    public function manageAutoPublishControl(Publishable $document, $locations, \DateTime $autoPublishDate = null,$goodyStar=FALSE)
    {

        if (!($autoPublishDate instanceof \DateTime) || $autoPublishDate < new \DateTime()) {
            return array('status' => 'error', 'message' => $this->container->get('translator')->trans('Please specify a publish date after today'));
        }

        $newLocationsSections = array();
        foreach ($locations as $location) {
            $newLocationsSections[] = $location->getSection();
        }
        $oldPublishLocations = $document->getPublishLocations();
        $oldLocationsSections = array();
        foreach ($oldPublishLocations as $publishLocation) {
            $locationInfo = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('section' => $publishLocation->getSection()));
            $location = $locationInfo[0];

            //this condition to ignore the locations that's selected by default
            if ($location->getIsSelectable()) {
                $oldLocationsSections[] = $publishLocation->getSection();
            }
        }

        $removedLocationsSections = array_diff($oldLocationsSections, $newLocationsSections);
        $adddedLocationsSections = array_diff($newLocationsSections, $oldLocationsSections);

        foreach ($removedLocationsSections as $section) {
            $targetLocationObject;
            foreach ($oldPublishLocations as $location) {
                if ($location->getSection() == $section)
                    $targetLocationObject = $location;
            }
            $this->unpublishFromLocation($document, $targetLocationObject, false);
        }

        foreach ($adddedLocationsSections as $section) {

            $locationInfo = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findBy(array('section' => $section));

            $location = $locationInfo[0];

            $this->addPublishLocation($document, $location->getPublishedLocationObject($this->getUser(), $autoPublishDate));
        }

        $document->setPublishedBy($this->getUser());
        $document->setAutoPublishDate($autoPublishDate);
        $document->setGoodyStar($goodyStar);

        $this->dm->flush();

        return array("status" => 'success', "message" => $this->translator->trans('recipe will be published at %datetime%', array('%datetime%' => $document->getAutoPublishDate()->format('Y-m-d h:i A'))));
    }

    protected function validateToPublish(Publishable $document, array $locations)
    {

    }

    protected function readyLocationVacancy(PublishLocation $location, $maxNumberInLocation)
    {
        $documents = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:'.$this->type)
                ->field('status')->equals('publish')
                ->field('publishLocations.section')->equals($location->getSection())
                ->field('publishLocations.page')->equals($location->getPage())
                ->getQuery()->execute();

        if (count($documents) >= $maxNumberInLocation) {
            $oldestDocument = null;
            $oldestLocation = null;
            $oldestDate = "";

            foreach ($documents as $document) {
                foreach ($document->getPublishLocations() as $publishLocation) {
                    if ($publishLocation->getSection() == $location->getSection()) {
                        if ($oldestDocument == null || $publishLocation->getPublishedAt() < $oldestDate) {
                            $oldestDocument = $document;
                            $oldestLocation = $publishLocation;
                            $oldestDate = $publishLocation->getPublishedAt();
                        }
                    }
                }
            }

            $this->unpublishFromLocation($oldestDocument, $oldestLocation, TRUE, FALSE);
        }
    }

    public function delete($recipe,$reason = NULL)
    {

        $userFrom = $this->container->get('security.token_storage')->getToken()->getUser();

        $isValid = $this->validateDelete($recipe);

        if ($isValid['status'] == 'success') {

            if ($recipe->getStatus() == 'published' || $recipe->getStatus() == 'autopublish') {
                $this->container->get('recipe_operations')->unpublish($recipe);
            }
            $recipe
                ->setStatus('deleted')
                ->setDeletedAt(new \DateTime())
                ->setDeletedBy($userFrom)
                ->setAssignedTo(NULL)
                ->setReason($reason);
            if ($recipe->getStatus() == 'published') {
                $this->container->get('redirect')->removeRedirect($this->getFrontEndUrl($recipe));
                $this->hideFrontEndUrl($recipe);
            }
            $this->dm->flush();
        }
        return $isValid;
    }

    public function validateDelete($recipe)
    {

    }
}
