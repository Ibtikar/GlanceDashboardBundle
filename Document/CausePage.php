<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContextInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\hasLifeCycleCallbacks
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\CausePageRepository")
 */
class CausePage extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 2000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $brief;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 2000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $briefEn;


    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $profilePhoto;

    /**
     * @MongoDB\String
     */
    private $defaultProfilePhoto;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Competition", simple=true)
     */
    protected $competition;


    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $metaTagTitleAr;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $metaTagDesciptionAr;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $metaTagTitleEn;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $metaTagDesciptionEn;


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
     * Set brief
     *
     * @param string $brief
     * @return self
     */
    public function setBrief($brief)
    {
        $this->brief = $brief;
        return $this;
    }

    /**
     * Get brief
     *
     * @return string $brief
     */
    public function getBrief()
    {
        return $this->brief;
    }

    /**
     * Set briefEn
     *
     * @param string $briefEn
     * @return self
     */
    public function setBriefEn($briefEn)
    {
        $this->briefEn = $briefEn;
        return $this;
    }

    /**
     * Get briefEn
     *
     * @return string $briefEn
     */
    public function getBriefEn()
    {
        return $this->briefEn;
    }



    /**
     * Set profilePhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto
     * @return self
     */
    public function setProfilePhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto = null)
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
     * Set defaultProfilePhoto
     *
     * @param string $defaultProfilePhoto
     * @return self
     */
    public function setDefaultProfilePhoto($defaultProfilePhoto)
    {
        $this->defaultProfilePhoto = $defaultProfilePhoto;
        return $this;
    }

    /**
     * Get defaultProfilePhoto
     *
     * @return string $defaultProfilePhoto
     */
    public function getDefaultProfilePhoto()
    {
        return $this->defaultProfilePhoto;
    }





    /**
     * Set competition
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Competition $competition
     * @return self
     */
    public function setCompetition(\Ibtikar\GlanceDashboardBundle\Document\Competition $competition)
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * Get competition
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Competition $competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }


     /**
     * Set metaTagTitleAr
     *
     * @param string $metaTagTitleAr
     * @return self
     */
    public function setMetaTagTitleAr($metaTagTitleAr) {
        $this->metaTagTitleAr = $metaTagTitleAr;
        return $this;
    }

    /**
     * Get metaTagTitleAr
     *
     * @return string $metaTagTitleAr
     */
    public function getMetaTagTitleAr() {
        return $this->metaTagTitleAr;
    }

    /**
     * Set metaTagDesciptionAr
     *
     * @param string $metaTagDesciptionAr
     * @return self
     */
    public function setMetaTagDesciptionAr($metaTagDesciptionAr) {
        $this->metaTagDesciptionAr = $metaTagDesciptionAr;
        return $this;
    }

    /**
     * Get metaTagDesciptionAr
     *
     * @return string $metaTagDesciptionAr
     */
    public function getMetaTagDesciptionAr() {
        return $this->metaTagDesciptionAr;
    }

    /**
     * Set metaTagTitleEn
     *
     * @param string $metaTagTitleEn
     * @return self
     */
    public function setMetaTagTitleEn($metaTagTitleEn) {
        $this->metaTagTitleEn = $metaTagTitleEn;
        return $this;
    }

    /**
     * Get metaTagTitleEn
     *
     * @return string $metaTagTitleEn
     */
    public function getMetaTagTitleEn() {
        return $this->metaTagTitleEn;
    }

    /**
     * Set metaTagDesciptionEn
     *
     * @param string $metaTagDesciptionEn
     * @return self
     */
    public function setMetaTagDesciptionEn($metaTagDesciptionEn) {
        $this->metaTagDesciptionEn = $metaTagDesciptionEn;
        return $this;
    }

    /**
     * Get metaTagDesciptionEn
     *
     * @return string $metaTagDesciptionEn
     */
    public function getMetaTagDesciptionEn() {
        return $this->metaTagDesciptionEn;
    }
}
