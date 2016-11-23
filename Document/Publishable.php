<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @MongoDB\Document
 */
abstract class Publishable extends Document
{

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Date
     */
    private $publishedAt;

    /**
     * @MongoDB\Date
     */
    private $unpublishedAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Staff")
     */
    private $publishedBy;

    /**
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\PublishLocation")
     */
    private $publishLocations;

    /**
     * @MongoDB\Increment
     */
    private $noOfShares = 0;

    /**
     * @MongoDB\Boolean
     */
    private $sendPushNotification = false;

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return string the translation in the list unpublish action
     */
    abstract public function getDocumentTranslation();

    private function initializeVariables() {
        if (!$this->publishLocations) {
            $this->publishLocations = new ArrayCollection();
        }
    }

    /**
     * Set publishedAt
     *
     * @param date $publishedAt
     * @return self
     */
    public function setPublishedAt($publishedAt) {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return date $publishedAt
     */
    public function getPublishedAt() {
        return $this->publishedAt;
    }

    /**
     * Set unpublishedAt
     *
     * @param date $unpublishedAt
     * @return self
     */
    public function setUnpublishedAt($unpublishedAt) {
        $this->unpublishedAt = $unpublishedAt;
        return $this;
    }

    /**
     * Get unpublishedAt
     *
     * @return date $unpublishedAt
     */
    public function getUnpublishedAt() {
        return $this->unpublishedAt;
    }

    /**
     * Add publishLocation
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\PublishLocation $publishLocation
     */
    public function addPublishLocation(\Ibtikar\GlanceDashboardBundle\Document\PublishLocation $publishLocation) {
        $this->initializeVariables();
        $this->publishLocations[] = $publishLocation;
    }

    /**
     * Remove publishLocation
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\PublishLocation $publishLocation
     */
    public function removePublishLocation(\Ibtikar\GlanceDashboardBundle\Document\PublishLocation $publishLocation) {
        $this->initializeVariables();
        $this->publishLocations->removeElement($publishLocation);
    }

    /**
     * Get publishLocations
     *
     * @return Doctrine\Common\Collections\Collection $publishLocations
     */
    public function getPublishLocations() {
        $this->initializeVariables();
        return $this->publishLocations;
    }

    /**
     * Set publishedBy
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy
     * @return self
     */
    public function setPublishedBy(\Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy) {
        $this->publishedBy = $publishedBy;
        return $this;
    }

    /**
     * Get publishedBy
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy
     */
    public function getPublishedBy() {
        return $this->publishedBy;
    }

    public function getPublish() {
        return ($this->getStatus() == 'published') ? true : false;
    }

    /**
     * @return string
     */
    abstract public function getSlug();

    /**
     * Set noOfShares
     *
     * @param int $noOfShares
     * @return self
     */
    public function setNoOfShares($noOfShares) {
        $this->noOfShares = $noOfShares;
        return $this;
    }

    /**
     * Get noOfShares
     *
     * @return int $noOfShares
     */
    public function getNoOfShares() {
        return $this->noOfShares;
    }

   public function getAutopublish() {
        return ($this->getStatus() == 'autopublish') ? true : false;
    }

    /**
     * Set sendPushNotification
     *
     * @param boolean $sendPushNotification
     * @return self
     */
    public function setSendPushNotification($sendPushNotification) {
        $this->sendPushNotification = $sendPushNotification;
        return $this;
    }

    /**
     * Get sendPushNotification
     *
     * @return boolean $sendPushNotification
     */
    public function getSendPushNotification() {
        return $this->sendPushNotification;
    }

}
