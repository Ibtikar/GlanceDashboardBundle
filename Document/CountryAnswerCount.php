<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class CountryAnswerCount {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Country", simple=true)
     */
    protected $country;

    /**
     * @MongoDB\Increment
     */
    private $count = 1;


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
     * Set country
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Country $country
     * @return self
     */
    public function setCountry(\Ibtikar\GlanceUMSBundle\Document\Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set count
     *
     * @param increment $count
     * @return self
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Get count
     *
     * @return increment $count
     */
    public function getCount()
    {
        return $this->count;
    }
}
