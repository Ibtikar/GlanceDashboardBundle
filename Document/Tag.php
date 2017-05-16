<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;

/**
 * @MongoDB\Document
 * @MongoDBUnique(fields={"name"})
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 * })
 */
class Tag extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 330,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $name;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 330,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $tag;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 330,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $tagEn;

    /**
     * @MongoDB\String
     */
    private $slug;

    /**
     * @MongoDB\Increment
     */
    private $usageNumber = 0;

    /**
     * @MongoDB\String
     */
    private $defaultLocation;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    public function __toString() {
        return "$this->name";
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        $slug = ArabicMongoRegex::slugify($name);
        $this->setSlug($slug);
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set usageNumber
     *
     * @param int $usageNumber
     * @return self
     */
    public function setUsageNumber($usageNumber) {
        $this->usageNumber = $usageNumber;
        return $this;
    }

    /**
     * Get usageNumber
     *
     * @return int $usageNumber
     */
    public function getUsageNumber() {
        return $this->usageNumber;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set defaultLocation
     *
     * @param boolean $defaultLocation
     * @return self
     */
    public function setDefaultLocation($defaultLocation)
    {
        $this->defaultLocation = $defaultLocation;
        return $this;
    }

    /**
     * Get defaultLocation
     *
     * @return boolean $defaultLocation
     */
    public function getDefaultLocation()
    {
        return $this->defaultLocation;
    }

    /**
     * Set tag
     *
     * @param string $tag
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Get tag
     *
     * @return string $tag
     */
        public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set tagEn
     *
     * @param string $tagEn
     * @return self
     */
    public function setTagEn($tagEn)
    {
        $this->tagEn = $tagEn;
        return $this;
    }

    /**
     * Get tagEn
     *
     * @return string $tagEn
     */
    public function getTagEn()
    {
        return $this->tagEn;
    }
}
