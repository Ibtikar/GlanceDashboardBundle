<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\SlugRepository")
 * @MongoDBUnique(fields={"slugAr"})
 * @MongoDBUnique(fields={"slugEn"})
 * @MongoDB\Index(keys={"slug"="asc"})
 */
class Slug extends Document
{

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $type;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $referenceId;


    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $slugAr;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $slugEn;

    /**
     * @MongoDB\Boolean
     */
    private $publish = false;
    static $TYPE_CATEGORY = "Category";
    static $TYPE_RECIPE = "recipe";
    static $TYPE_ARTICLE = "Article";
    static $TYPE_TIP = "Tip";
    static $TYPE_PRODUCT = "Product";
    static $TYPE_KITCHEN911 = "Kitchen911";
    static $TYPE_Rout = "Rout";
    static  $TYPE_SHORTURL = 'shortUrl';
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
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set referenceId
     *
     * @param string $referenceId
     * @return self
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
        return $this;
    }

    /**
     * Get referenceId
     *
     * @return string $referenceId
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }


    /**
     * Set publish
     *
     * @param boolean $publish
     * @return self
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;
        return $this;
    }

    /**
     * Get publish
     *
     * @return boolean $publish
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * Set slugAr
     *
     * @param string $slugAr
     * @return self
     */
    public function setSlugAr($slugAr)
    {
        $this->slugAr = $slugAr;
        return $this;
    }

    /**
     * Get slugAr
     *
     * @return string $slugAr
     */
    public function getSlugAr()
    {
        return $this->slugAr;
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
}
