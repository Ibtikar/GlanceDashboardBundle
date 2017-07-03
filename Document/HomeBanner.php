<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 */
class HomeBanner extends Document
{

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $bannerPhoto;

    /**
     * @MongoDB\String
     */
    private $bannerUrl;

    /**
     * @MongoDB\Boolean
     */
    private $show = false;

    /**
     * @MongoDB\String
     */
    private $name;
    
    /**
     * @MongoDB\String
     */
    private $shortName;
    
    /**
     * @MongoDB\String
     */
    private $description;
    /**
     * @MongoDB\String
     */
    private $descriptionEn;

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
     * Set bannerPhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $bannerPhoto
     * @return self
     */
    public function setBannerPhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $bannerPhoto= Null)
    {
        $this->bannerPhoto = $bannerPhoto;
        return $this;
    }

    /**
     * Get bannerPhoto
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Media $bannerPhoto
     */
    public function getBannerPhoto()
    {
        return $this->bannerPhoto;
    }

    /**
     * Set bannerUrl
     *
     * @param string $bannerUrl
     * @return self
     */
    public function setBannerUrl($bannerUrl)
    {
        $this->bannerUrl = $bannerUrl;
        return $this;
    }

    /**
     * Get bannerUrl
     *
     * @return string $bannerUrl
     */
    public function getBannerUrl()
    {
        return $this->bannerUrl;
    }

    /**
     * Set show
     *
     * @param boolean $show
     * @return self
     */
    public function setShow($show)
    {
        $this->show = $show;
        return $this;
    }

    /**
     * Get show
     *
     * @return boolean $show
     */
    public function getShow()
    {
        return $this->show;
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
     * Set shortName
     *
     * @param string $shortName
     * @return self
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * Get shortName
     *
     * @return string $shortName
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set descriptionEn
     *
     * @param string $descriptionEn
     * @return self
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;
        return $this;
    }

    /**
     * Get descriptionEn
     *
     * @return string $descriptionEn
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }
}
