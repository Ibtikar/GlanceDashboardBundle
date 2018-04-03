<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\CategoryRepository")
 * @MongoDBUnique(fields={"name"})
 * @MongoDBUnique(fields={"nameEn"})
 * @MongoDB\HasLifecycleCallbacks
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 *   @MongoDB\Index(keys={"nameEn"="asc"})
 * })
 */
class RecipeSubCategory extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $nameEn;

    /**
     * @MongoDB\String
     */
    private $slug;

    public function __toString() {
        return (string) $this->name;
    }

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
