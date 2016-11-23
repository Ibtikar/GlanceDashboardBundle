<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"oldUrl"="asc"}),
 *   @MongoDB\Index(keys={"redirectToUrl"="asc"})
 * })
 */
class Redirect extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\String
     */
    private $oldUrl;

    /**
     * @MongoDB\String
     */
    private $redirectToUrl;

    /**
     * @MongoDB\Int
     */
    private $statusCode = 302;

    /**
     * @MongoDB\Increment
     */
    private $accessCount = 0;

    /**
     * @MongoDB\Date
     */
    private $lastAccessedAt;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set oldUrl
     *
     * @param string $oldUrl
     * @return self
     */
    public function setOldUrl($oldUrl) {
        $this->oldUrl = $oldUrl;
        return $this;
    }

    /**
     * Get oldUrl
     *
     * @return string $oldUrl
     */
    public function getOldUrl() {
        return $this->oldUrl;
    }

    /**
     * Set redirectToUrl
     *
     * @param string $redirectToUrl
     * @return self
     */
    public function setRedirectToUrl($redirectToUrl) {
        $this->redirectToUrl = $redirectToUrl;
        return $this;
    }

    /**
     * Get redirectToUrl
     *
     * @return string $redirectToUrl
     */
    public function getRedirectToUrl() {
        return $this->redirectToUrl;
    }

    /**
     * Set statusCode
     *
     * @param int $statusCode
     * @return self
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get statusCode
     *
     * @return int $statusCode
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Set accessCount
     *
     * @param increment $accessCount
     * @return self
     */
    public function setAccessCount($accessCount) {
        $this->accessCount = $accessCount;
        return $this;
    }

    /**
     * Get accessCount
     *
     * @return increment $accessCount
     */
    public function getAccessCount() {
        return $this->accessCount;
    }

    /**
     * Set lastAccessedAt
     *
     * @param date $lastAccessedAt
     * @return self
     */
    public function setLastAccessedAt($lastAccessedAt) {
        $this->lastAccessedAt = $lastAccessedAt;
        return $this;
    }

    /**
     * Get lastAccessedAt
     *
     * @return date $lastAccessedAt
     */
    public function getLastAccessedAt() {
        return $this->lastAccessedAt;
    }

}
