<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\CityRepository")
 * @MongoDBUnique(fields={"name"})
 * @MongoDBUnique(fields={"name_en"})
 * @MongoDBUnique(fields={"slug"})
 * @MongoDB\HasLifecycleCallbacks
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 *   @MongoDB\Index(keys={"name_en"="asc"})
 * })
 */
class City extends Document {

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
     *      max = 330,
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
     *      max = 330,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $name_en;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Country", simple=true)
     */
    protected $country;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Regex(pattern="/^[a-zA-Z\x{0600}-\x{06ff}\-]+$/u", message="only characters and dashes allowed")
     * @Assert\Length(
     *      max = 125,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slug;

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
     * @MongoDB\String
     */
    private $defaultCoverPhoto;

    /**
     * @MongoDB\String
     */
    private $defaultProfilePhoto;

    /**
     * @MongoDB\Float
     */
    private $lat = 24.65096417661143;

    /**
     * @MongoDB\Float
     */
    private $long = 46.742587867920065;

    /**
     * @MongoDB\String
     */
    private $timeZone;

    /**
     * @MongoDB\String
     * @Assert\Type(type="numeric",message = "the number must be numeric")
     */
    private $areaCode;

    /**
     * @MongoDB\Increment
     */
    private $staffMembersCount = 0;

    /**
     * @MongoDB\Increment
     */
    private $newsCount = 0;

    /**
     * @MongoDB\Increment
     */
    private $visitorsCount = 0;

    /**
     * @MongoDB\Increment
     */
    private $usersCount = 0;


    /**
     * @MongoDB\Increment
     */
    private $placesCount = 0;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    protected $metaTag;


    public function getPath() {
        return (string) $this->name;
    }


    public static function getValidTimeZone() {
        return array('UTC-12' => 'UTC-12', 'UTC-11' => 'UTC-11', 'UTC-10' => 'UTC-10', 'UTC-9'=>'UTC-9', 'UTC-8'=>'UTC-8', 'UTC-7'=>'UTC-7', 'UTC-6'=> 'UTC-6'
            , 'UTC-5'=>'UTC-5', 'UTC-4'=>'UTC-4', 'UTC-3'=>'UTC-3', 'UTC-2'=>'UTC-2', 'UTC-1'=>'UTC-1', 'UTC+0'=>'UTC+0', 'UTC+1'=>'UTC+1', 'UTC+2'=>'UTC+2'
            , 'UTC+3'=>'UTC+3', 'UTC+4'=>'UTC+4', 'UTC+5'=>'UTC+5', 'UTC+6'=>'UTC+6', 'UTC+7'=>'UTC+7', 'UTC+8'=>'UTC+8', 'UTC+9'=>'UTC+9', 'UTC+10'=>'UTC+10'
            , 'UTC+11'=>'UTC+11', 'UTC+12'=>'UTC+12', 'UTC+13'=>'UTC+13', 'UTC+14'=>'UTC+14');
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

    /**
     * Set nameEn
     *
     * @param string $nameEn
     * @return self
     */
    public function setNameEn($nameEn) {
        $this->name_en = $nameEn;
        return $this;
    }

    /**
     * Get nameEn
     *
     * @return string $nameEn
     */
    public function getNameEn() {
        return $this->name_en;
    }

    /**
     * Set staffMembersCount
     *
     * @param increment $staffMembersCount
     * @return self
     */
    public function setStaffMembersCount($staffMembersCount) {
        $this->staffMembersCount = $staffMembersCount;
        return $this;
    }

    /**
     * Get staffMembersCount
     *
     * @return increment $staffMembersCount
     */
    public function getStaffMembersCount() {
        return $this->staffMembersCount;
    }

    /**
     * Set usersCount
     *
     * @param increment $usersCount
     * @return self
     */
    public function setUsersCount($usersCount) {
        $this->usersCount = $usersCount;
        return $this;
    }

    /**
     * Get usersCount
     *
     * @return increment $usersCount
     */
    public function getUsersCount() {
        return $this->usersCount;
    }

    /**
     * Set visitorsCount
     *
     * @param increment $visitorsCount
     * @return self
     */
    public function setVisitorsCount($visitorsCount) {
        $this->visitorsCount = $visitorsCount;
        $this->usersCount = $this->staffMembersCount + $this->visitorsCount;
        return $this;
    }

    /**
     * Get visitorsCount
     *
     * @return increment $visitorsCount
     */
    public function getVisitorsCount() {
        return $this->visitorsCount;
    }

    /**
     * Set country
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Country $country
     * @return self
     */
    public function setCountry($country) {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Country $country
     */
    public function getCountry() {
        return $this->country;
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
     * Set defaultCoverPhoto
     *
     * @param string $defaultCoverPhoto
     * @return self
     */
    public function setDefaultCoverPhoto($defaultCoverPhoto)
    {
        $this->defaultCoverPhoto = $defaultCoverPhoto;
        return $this;
    }

    /**
     * Get defaultCoverPhoto
     *
     * @return string $defaultCoverPhoto
     */
    public function getDefaultCoverPhoto()
    {
        return $this->defaultCoverPhoto;
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
     * Set lat
     *
     * @param float $lat
     * @return self
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * Get lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set long
     *
     * @param float $long
     * @return self
     */
    public function setLong($long)
    {
        $this->long = $long;
        return $this;
    }

    /**
     * Get long
     *
     * @return float $long
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * Set timeZone
     *
     * @param int $timeZone
     * @return self
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    /**
     * Get timeZone
     *
     * @return int $timeZone
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Set areaCode
     *
     * @param int $areaCode
     * @return self
     */
    public function setAreaCode($areaCode)
    {
        $this->areaCode = $areaCode;
        return $this;
    }

    /**
     * Get areaCode
     *
     * @return int $areaCode
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }

    /**
     * Set newsCount
     *
     * @param increment $newsCount
     * @return self
     */
    public function setNewsCount($newsCount) {
        $this->newsCount = $newsCount;
        return $this;
    }

    /**
     * Get newsCount
     *
     * @return increment $newsCount
     */
    public function getNewsCount() {
        return $this->newsCount;
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
     * Set placesCount
     *
     * @param int $placesCount
     * @return self
     */
    public function setPlacesCount($placesCount) {
        $this->placesCount = $placesCount;
        return $this;
    }

    /**
     * Get placesCount
     *
     * @return int $placesCount
     */
    public function getPlacesCount() {
        return $this->placesCount;
    }

    /**
     * Set metaTag
     *
     * @param string $metaTag
     * @return self
     */
    public function setMetaTag($metaTag)
    {
        $this->metaTag = $metaTag;
        return $this;
    }

    /**
     * Get metaTag
     *
     * @return string $metaTag
     */
    public function getMetaTag()
    {
        return $this->metaTag;
    }


}
