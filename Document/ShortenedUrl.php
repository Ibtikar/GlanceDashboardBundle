<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"url"="asc"}),
 *   @MongoDB\Index(keys={"shortCode"="asc"}),
 * })
 */
class ShortenedUrl extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\String
     */
    private $url;

    /**
     * @MongoDB\String
     */
    private $shortCode;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return self
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set shortCode
     *
     * @param string $shortCode
     * @return self
     */
    public function setShortCode($shortCode) {
        $this->shortCode = $shortCode;
        return $this;
    }

    /**
     * Get shortCode
     *
     * @return string $shortCode
     */
    public function getShortCode() {
        return $this->shortCode;
    }

}
