<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;


/**
 * @MongoDB\EmbeddedDocument
 */
class PublishLocation {


    /**
     * @MongoDB\Id
     */
    private $id;


    /**
     * @MongoDB\String
     */
    private $page;

    /**
     * @MongoDB\String
     */
    private $section;

    /**
     * @MongoDB\Date
     */
    private $publishedAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Staff")
     */
    private $publishedBy;







    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set page
     *
     * @param string $page
     * @return self
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get page
     *
     * @return string $page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set section
     *
     * @param string $section
     * @return self
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get section
     *
     * @return string $section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set publishedAt
     *
     * @param date $publishedAt
     * @return self
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return date $publishedAt
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set publishedBy
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy
     * @return self
     */
    public function setPublishedBy(\Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy)
    {
        $this->publishedBy = $publishedBy;
        return $this;
    }

    /**
     * Get publishedBy
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy
     */
    public function getPublishedBy()
    {
        return $this->publishedBy;
    }
}
