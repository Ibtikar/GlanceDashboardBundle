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
 * @MongoDBUnique(fields={"nameEn"})
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 *   @MongoDB\Index(keys={"nameEn"="asc"}),
 * })
 */
class RecipeTag extends Document {

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
     */
    private $slug;

        /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 330,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $nameEn;

    /**
     * @MongoDB\String
     */
    private $slugEn;

    /**
     * @MongoDB\Increment
     */
    private $usageNumber = 0;

    /**
     * @MongoDB\String
     */
    private $metaTagTitleAr;

    /**
     * @MongoDB\String
     */
    private $metaTagDesciptionAr;

    /**
     * @MongoDB\String
     */
    private $metaTagTitleEn;

    /**
     * @MongoDB\String
     */
    private $metaTagDesciptionEn;

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
     * Set nameEn
     *
     * @param string $nameEn
     * @return self
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
        return $this;
    }

    /**
     * Get nameEn
     *
     * @return string $nameEn
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * Set slugEn
     *
     * @param string $slugEn
     * @return self
     */
    public function setSlugEn($slugEn)
    {
        $this->slugEn = $slugEn;
        return $this;
    }

    /**
     * Get slugEn
     *
     * @return string $slugEn
     */
    public function getSlugEn()
    {
        return $this->slugEn;
    }

     /**
     * Set metaTagTitleAr
     *
     * @param string $metaTagTitleAr
     * @return self
     */
    public function setMetaTagTitleAr($metaTagTitleAr)
    {
        $this->metaTagTitleAr = $metaTagTitleAr;
        return $this;
    }

    /**
     * Get metaTagTitleAr
     *
     * @return string $metaTagTitleAr
     */
    public function getMetaTagTitleAr()
    {
        return $this->metaTagTitleAr;
    }

    /**
     * Set metaTagDesciptionAr
     *
     * @param string $metaTagDesciptionAr
     * @return self
     */
    public function setMetaTagDesciptionAr($metaTagDesciptionAr)
    {
        $this->metaTagDesciptionAr = $metaTagDesciptionAr;
        return $this;
    }

    /**
     * Get metaTagDesciptionAr
     *
     * @return string $metaTagDesciptionAr
     */
    public function getMetaTagDesciptionAr()
    {
        return $this->metaTagDesciptionAr;
    }

    /**
     * Set metaTagTitleEn
     *
     * @param string $metaTagTitleEn
     * @return self
     */
    public function setMetaTagTitleEn($metaTagTitleEn)
    {
        $this->metaTagTitleEn = $metaTagTitleEn;
        return $this;
    }

    /**
     * Get metaTagTitleEn
     *
     * @return string $metaTagTitleEn
     */
    public function getMetaTagTitleEn()
    {
        return $this->metaTagTitleEn;
    }

    /**
     * Set metaTagDesciptionEn
     *
     * @param string $metaTagDesciptionEn
     * @return self
     */
    public function setMetaTagDesciptionEn($metaTagDesciptionEn)
    {
        $this->metaTagDesciptionEn = $metaTagDesciptionEn;
        return $this;
    }

    /**
     * Get metaTagDesciptionEn
     *
     * @return string $metaTagDesciptionEn
     */
    public function getMetaTagDesciptionEn()
    {
        return $this->metaTagDesciptionEn;
    }
}
