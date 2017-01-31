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
 * @MongoDB\hasLifeCycleCallbacks
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}),
 *   @MongoDB\Index(keys={"nameEn"="asc"})
 * })
 */
class Product extends Document
{

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

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $relatedRecipe;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $relatedKitchen911;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $relatedTip;

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

    public function __toString()
    {
        return (string) $this->name;
    }

    public function getRelatedRecipeJson()
    {
        $array = array();
        if ($this->getRelatedRecipe()) {
            foreach ($this->getRelatedRecipe() as $recipe) {
                $array[] = array(
                    'id' => $recipe->getId(),
                    'text' => $recipe->getTitle(),
                    'img' => $this->getDefaultCoverPhoto($recipe)

                );
            }
        }
        return $array;
    }

    public function getRelatedKitchen911Json()
    {
        $array = array();
        if ($this->getRelatedKitchen911()) {
            foreach ($this->getRelatedKitchen911() as $article) {
                $array[] = array(
                    'id' => $article->getId(),
                    'text' => $article->getTitle(),
                    'img' => $this->getDefaultCoverPhoto($article)

                );
            }
        }
        return $array;
    }

    public function getRelatedTipJson()
    {
        $array = array();
        if ($this->getRelatedTip()) {
            foreach ($this->getRelatedTip() as $tip) {
                $array[] = array(
                    'id' => $tip->getId(),
                    'text' => $tip->getTitle(),
                    'img' => $this->getDefaultCoverPhoto($tip)

                );
            }
        }
        return $array;
    }

    public function getDefaultCoverPhoto($document){
        if($document->getCoverPhoto()){
            $type=$document->getCoverPhoto()->getType();
            if($type=='image'){
            return  '/'.$document->getCoverPhoto()->getWebPath()   ;
            }else{
                return  'https://i.ytimg.com/vi/' . $document->getCoverPhoto()->getVid() . '/default.jpg' ;
            }

        }
        return '';
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
     * Set coverPhoto
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $coverPhoto
     * @return self
     */
    public function setCoverPhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $coverPhoto = NULL)
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
    public function setProfilePhoto(\Ibtikar\GlanceDashboardBundle\Document\Media $profilePhoto = NULL)
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

    public function __construct()
    {
        $this->relatedRecipe = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedKitchen911 = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedTip = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add relatedRecipe
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedRecipe
     */
    public function addRelatedRecipe(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedRecipe)
    {
        $this->relatedRecipe[] = $relatedRecipe;
    }
    /**
     * Add relatedRecipe
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedRecipe
     */
    public function setRelatedRecipe($relatedRecipe=array())
    {
        $this->relatedRecipe = $relatedRecipe;
    }

    /**
     * Remove relatedRecipe
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedRecipe
     */
    public function removeRelatedRecipe(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedRecipe)
    {
        $this->relatedRecipe->removeElement($relatedRecipe);
    }

    /**
     * Get relatedRecipe
     *
     * @return \Doctrine\Common\Collections\Collection $relatedRecipe
     */
    public function getRelatedRecipe()
    {
        return $this->relatedRecipe;
    }

    /**
     * Add relatedKitchen911
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedKitchen911
     */
    public function addRelatedKitchen911(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedKitchen911)
    {
        $this->relatedKitchen911[] = $relatedKitchen911;
    }
    /**
     * Add relatedKitchen911
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedKitchen911
     */
    public function setRelatedKitchen911($relatedKitchen911 =array())
    {
        $this->relatedKitchen911 = $relatedKitchen911;
    }

    /**
     * Remove relatedKitchen911
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedKitchen911
     */
    public function removeRelatedKitchen911(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedKitchen911)
    {
        $this->relatedKitchen911->removeElement($relatedKitchen911);
    }

    /**
     * Get relatedKitchen911
     *
     * @return \Doctrine\Common\Collections\Collection $relatedKitchen911
     */
    public function getRelatedKitchen911()
    {
        return $this->relatedKitchen911;
    }

    /**
     * Add relatedTip
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedTip
     */
    public function addRelatedTip(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedTip)
    {
        $this->relatedTip[] = $relatedTip;
    }
    /**
     * Add relatedTip
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedTip
     */
    public function setRelatedTip($relatedTip=array())
    {
        $this->relatedTip = $relatedTip;
    }

    /**
     * Remove relatedTip
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedTip
     */
    public function removeRelatedTip(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedTip)
    {
        $this->relatedTip->removeElement($relatedTip);
    }

    /**
     * Get relatedTip
     *
     * @return \Doctrine\Common\Collections\Collection $relatedTip
     */
    public function getRelatedTip()
    {
        return $this->relatedTip;
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
}
