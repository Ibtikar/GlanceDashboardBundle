<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContextInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\hasLifeCycleCallbacks
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\OnlineAcademyRepository")
 */
class OnlineAcademy extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $profilePhoto;

    /**
     * @MongoDB\String
     */
    private $name;

    /**
     * @MongoDB\String
     */
    private $defaultProfilePhoto;

    /**
     * @MongoDB\String
     * @Assert\Url
     */
    protected $url;

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
    public function getId() {
        return $this->id;
    }

    /**
     * Set profilePhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto
     * @return self
     */
    public function setProfilePhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto = null) {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    /**
     * Get profilePhoto
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto
     */
    public function getProfilePhoto() {
        return $this->profilePhoto;
    }

    /**
     * Set defaultProfilePhoto
     *
     * @param string $defaultProfilePhoto
     * @return self
     */
    public function setDefaultProfilePhoto($defaultProfilePhoto) {
        $this->defaultProfilePhoto = $defaultProfilePhoto;
        return $this;
    }

    /**
     * Get defaultProfilePhoto
     *
     * @return string $defaultProfilePhoto
     */
    public function getDefaultProfilePhoto() {
        return $this->defaultProfilePhoto;
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
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName() {
        return $this->name;
    }


}
