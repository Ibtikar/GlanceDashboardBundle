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
class Category extends Document {

     /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $nameEn;



    /**
     * @Assert\NotBlank(groups={"create"})
     * @MongoDB\String
     * @Assert\Regex(pattern="/^[a-zA-Z\x{0600}-\x{06ff}\-]+$/u", message="only characters and dashes allowed")
     */
    private $slug;

    /**
     * @MongoDB\String
     * @Assert\Regex(pattern="/^[a-zA-Z\x{0600}-\x{06ff}\-]+$/u", message="only characters and dashes allowed")
     */
    private $slugEn;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Category", simple=true, nullable=true)
     * @Assert\NotBlank(groups={"subcategory"})
     */
    private $parent;


    /**
     * @MongoDB\Int
     */
    private $order ;

    /**
     * @MongoDB\Int
     */
    private $subcategoryNo=0 ;



    public function __toString() {
        return (string) $this->name;
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
     * Set parent
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Category $parent
     * @return self
     */
    public function setParent(\Ibtikar\GlanceDashboardBundle\Document\Category $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Category $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set order
     *
     * @param int $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set subcategoryNo
     *
     * @param int $subcategoryNo
     * @return self
     */
    public function setSubcategoryNo($subcategoryNo)
    {
        $this->subcategoryNo = $subcategoryNo;
        return $this;
    }

    /**
     * Get subcategoryNo
     *
     * @return int $subcategoryNo
     */
    public function getSubcategoryNo()
    {
        return $this->subcategoryNo;
    }
}
