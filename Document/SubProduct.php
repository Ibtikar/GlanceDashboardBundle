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
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 *   @MongoDB\Index(keys={"nameEn"="asc"})
 * })
 */
class SubProduct extends Document {

    public static $profileTypeChoices = array(
        "image" => "image",
        "video" => "video"
    );
    public static $TypeChoices = array(
        "activity" => "activity",
        "bestProduct" => "bestProduct"
    );

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 2,
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
     *      min = 2,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $nameEn;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 5,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $description;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 5,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $descriptionEn;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $profilePhoto;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $coverPhoto;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $naturalPhoto;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Product", simple=true)
     */
    private $product;


    /**
     * @MongoDB\Date
     */
    protected $editAt;
    /**
     * @MongoDB\String
     */
    protected $weight;
    /**
     * @MongoDB\String
     */
    protected $size;

    /**
     * @MongoDB\String
     */
    private $url;

    /**
     * @MongoDB\String
     */
    private $urlEn;

    /**
     * @MongoDB\String
     */
    private $type = 'subproduct';

    /**
     * @MongoDB\String
     */
    private $profileType = "image";

    /**
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Sponsor")
     */
    private $sponsors;


    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 125,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slug;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 125,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slugEn;

    public function __toString() {
        return (string) $this->name;
    }

    public function updateReferencesCounts($value) {
        $product = $this->getProduct();
        if ($product) {
            $product->setSubproductNo($product->getSubproductNo() + $value);
        }
    }


    public function getDefaultProfilePhotoVideoOrImage() {
        if ($this->profilePhoto) {
            $type = $this->profilePhoto->getType();
            if ($type == 'image') {
                return '/' . $this->profilePhoto->getWebPath();
            } else {
                return 'https://i.ytimg.com/vi/' . $this->profilePhoto->getVid() . '/default.jpg';
            }
        }
        return '';
    }

    /**
     * @return array
     */
    public static function getValidMediaTypes() {
        return array_keys(static::$profileTypeChoices);
    }

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
     * Set profilePhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto
     * @return self
     */
    public function setProfilePhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto =NULL)
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
     * Set product
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Product $product
     * @return self
     */
    public function setProduct(\Ibtikar\GlanceDashboardBundle\Document\Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Product $product
     */
    public function getProduct()
    {
        return $this->product;
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


    /**
     * Set weight
     *
     * @param float $weight
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * Get weight
     *
     * @return float $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set size
     *
     * @param float $size
     * @return self
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get size
     *
     * @return float $size
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
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
     * Set profileType
     *
     * @param string $profileType
     * @return self
     */
    public function setProfileType($profileType)
    {
        $this->profileType = $profileType;
        return $this;
    }

    /**
     * Get profileType
     *
     * @return string $profileType
     */
    public function getProfileType()
    {
        return $this->profileType;
    }

    /**
     * Set naturalPhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $naturalPhoto
     * @return self
     */
    public function setNaturalPhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $naturalPhoto)
    {
        $this->naturalPhoto = $naturalPhoto;
        return $this;
    }

    /**
     * Get naturalPhoto
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Media $naturalPhoto
     */
    public function getNaturalPhoto()
    {
        return $this->naturalPhoto;
    }

     public function __construct()
    {
        $this->sponsors = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add sponsor
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Sponsor $sponsor
     */
    public function addSponsor(\Ibtikar\GlanceDashboardBundle\Document\Sponsor $sponsor)
    {
        $this->sponsors[] = $sponsor;
    }

    /**
     * Remove sponsor
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Sponsor $sponsor
     */
    public function removeSponsor(\Ibtikar\GlanceDashboardBundle\Document\Sponsor $sponsor)
    {
        $this->sponsors->removeElement($sponsor);
    }

    /**
     * Get sponsors
     *
     * @return \Doctrine\Common\Collections\Collection $sponsors
     */
    public function getSponsors()
    {
        return $this->sponsors;
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
     * Set urlEn
     *
     * @param string $urlEn
     * @return self
     */
    public function setUrlEn($urlEn)
    {
        $this->urlEn = $urlEn;
        return $this;
    }

    /**
     * Get urlEn
     *
     * @return string $urlEn
     */
    public function getUrlEn()
    {
        return $this->urlEn;
    }
}
