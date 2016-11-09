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
 * @MongoDBUnique(fields={"name"})
 * @MongoDBUnique(fields={"nameEn"})
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 *   @MongoDB\Index(keys={"nameEn"="asc"})
 * })
 */
class Product extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $nameEn;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $description;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $descriptionEn;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $coverPhoto;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $profilePhoto;

    /**
     * @MongoDB\Increment
     */
    private $subproductNo = 0;

    /**
     * @MongoDB\Date
     */
    protected $editAt;

    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
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


    /**
     * Set coverPhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $coverPhoto
     * @return self
     */
    public function setCoverPhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $coverPhoto)
    {
        $this->coverPhoto = $coverPhoto;
        return $this;
    }

    /**
     * Get coverPhoto
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Media $coverPhoto
     */
    public function getCoverPhoto()
    {
        return $this->coverPhoto;
    }

    /**
     * Set profilePhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto
     * @return self
     */
    public function setProfilePhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto)
    {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    /**
     * Get profilePhoto
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto
     */
    public function getProfilePhoto()
    {
        return $this->profilePhoto;
    }

    /**
     * Set subproductNo
     *
     * @param increment $subproductNo
     * @return self
     */
    public function setSubproductNo($subproductNo)
    {
        $this->subproductNo = $subproductNo;
        return $this;
    }

    /**
     * Get subproductNo
     *
     * @return increment $subproductNo
     */
    public function getSubproductNo()
    {
        return $this->subproductNo;
    }

    /**
     * Set editAt
     *
     * @param date $editAt
     * @return self
     */
    public function setEditAt($editAt)
    {
        $this->editAt = $editAt;
        return $this;
    }

    /**
     * Get editAt
     *
     * @return date $editAt
     */
    public function getEditAt()
    {
        return $this->editAt;
    }
}
