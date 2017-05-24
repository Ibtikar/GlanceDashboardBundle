<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContextInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Publishable;
/**
 * @MongoDB\hasLifeCycleCallbacks
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\RecipeRepository")
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"status"="asc", "slugEn"="asc", "type"="asc", "deleted"="asc"}, options={"name"="get content by english slug"}),
 *   @MongoDB\Index(keys={"status"="asc", "slug"="asc", "type"="asc", "deleted"="asc"}, options={"name"="get content by arabic slug"}),
 * })
 */
class Recipe extends Publishable
{

    public static $difficultyMap = array(
        0 => 'easy',
        1 => 'medium',
        2 => 'difficult'
    );

    public static $courseMap = array(
      'Salad' => 'Salad',
      'Soup' => 'Soup',
      'Sandwich' => 'Sandwich',
      'Side Dish' => 'Side Dish',
      'Main Courses' => 'Main Courses',
      'Pastry' => 'Pastry',
      'Dessert' => 'Dessert',
      'Coffee Desserts' => 'Coffee Desserts',
      'Drink' => 'Drink'
    );

    public static $keyIngredientMap = array(
         'Minced' => 'Minced',
         'Beef' => 'Beef',
         'Lamb' => 'Lamb',
         'Chicken' => 'Chicken',
         'Seafood' => 'Seafood',
         'Vegetable' => 'Vegetable'
    );

    public static $mealMap = array(
        'Breakfast' => 'Breakfast',
        'Lunch' => 'Lunch',
        'Dinner' => 'Dinner'
    );

    public static $statuses = array(
        "new" => "new",
        "deleted" => "deleted",
        "publish" => "publish",
        "autopublish" => "autopublish",
        "draft" => "draft"
    );

    public static $types = array(
        "recipe" => "recipe",
        "article" => "article",
        "tip" => "tip",
        "kitchen911" => "kitchen911"
    );

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
    private $title;

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
    private $titleEn;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 300,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $brief;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 300,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $briefEn;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $ingredients;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $ingredientsEn;


        /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $text;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $textEn;


    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $method;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $methodEn;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Tag" , simple=true)
     */
    private $tags;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\RecipeTag" , simple=true)
     */
    private $recipeTags;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Tag" , simple=true)
     */
    private $tagsEn;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Staff")
     */
    private $chef;

    /**
     * @MongoDB\Int
     */
    private $preparationTime;

    /**
     * @MongoDB\Int
     */
    private $cookingTime;

    /**
     * @MongoDB\String
     */
    private $difficulty;

    /**
     * @MongoDB\Hash
     */
    private $course;

    /**
     * @MongoDB\Hash
     */
    private $meal;

    /**
     * @MongoDB\Hash
     */
    private $keyIngredient;

    /**
     * @MongoDB\Int
     */
    private $servingCount;

    /**
     * @MongoDB\Int
     */
    private $order;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Product" , simple=true)
     */
    private $products;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Country", simple=true)
     */
    protected $country;

    /**
     * @MongoDB\String
     */
    private $status = 'new';

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Staff")
     */
    private $assignedTo;

    /**
     * @MongoDB\String
     */
    private $type = 'recipe';

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $coverPhoto;

    /**
     * @MongoDB\String
     */
    private $defaultCoverPhoto;

    /**
     * @MongoDB\String
     */
    private $galleryType = 'sequence';

    /**
     * @MongoDB\Date
     */
    private $autoPublishDate;


    /**
     * @MongoDB\String
     */
    private $reason;

    /**
     * @MongoDB\Date
     */
    private $dailysolutionDate;

    /**
     * @MongoDB\Increment
     */
    private $noOfViews = 0;

    /**
     * @MongoDB\Increment
     */
    private $noOfLikes = 0;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slug;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slugEn;

    /**
     * @MongoDB\String
     */
    private $trackingNumber;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $relatedRecipe;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $relatedArticle;


    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $relatedTip;

    /**
     * @MongoDB\Boolean
     */
    protected $hideEnglishContent = false;

    /**
     * @MongoDB\Boolean
     */
    protected $goodyStar = false;

    /**
     * @MongoDB\Boolean
     */
    protected $migrated;

    /**
     * @MongoDB\String
     */
    protected $migrationData;

    public function __construct()
    {
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tagEn = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->trackingNumber = 'Recipe' . date('ymdHis') . rand(0, 99);
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getRelatedMaterialsJson(){
        $array = array();
        if($this->getRelatedRecipe()){
            foreach($this->getRelatedRecipe() as $material){
                $array[] = array(
                            'id'=>$material->getId(),
                            'title'=>$material->getTitle(),
                            'slug' =>$material->getSlug()
                        );
            }

        }
        return json_encode($array);
    }

    public function getDefaultCoverPhoto()
    {
        return $this->coverPhoto;
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
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set titleEn
     *
     * @param string $titleEn
     * @return self
     */
    public function setTitleEn($titleEn)
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    /**
     * Get titleEn
     *
     * @return string $titleEn
     */
    public function getTitleEn()
    {
        return $this->titleEn;
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
     * Set ingredients
     *
     * @param string $ingredients
     * @return self
     */
    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * Get ingredients
     *
     * @return string $ingredients
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

    /**
     * Set ingredientsEn
     *
     * @param string $ingredientsEn
     * @return self
     */
    public function setIngredientsEn($ingredientsEn)
    {
        $this->ingredientsEn = $ingredientsEn;
        return $this;
    }

    /**
     * Get ingredientsEn
     *
     * @return string $ingredientsEn
     */
    public function getIngredientsEn()
    {
        return $this->ingredientsEn;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get text
     *
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set ingredientsEn
     *
     * @param string $textEn
     * @return self
     */
    public function setTextEn($textEn)
    {
        $this->textEn = $textEn;
        return $this;
    }

    /**
     * Get textEn
     *
     * @return string $textEn
     */
    public function getTextEn()
    {
        return $this->textEn;
    }



    /**
     * Set method
     *
     * @param string $method
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get method
     *
     * @return string $method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set methodEn
     *
     * @param string $methodEn
     * @return self
     */
    public function setMethodEn($methodEn)
    {
        $this->methodEn = $methodEn;
        return $this;
    }

    /**
     * Get methodEn
     *
     * @return string $methodEn
     */
    public function getMethodEn()
    {
        return $this->methodEn;
    }

    /**
     * Add tag
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Tag $tag
     */
    public function addTag(\Ibtikar\GlanceDashboardBundle\Document\Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * Remove tag
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Tag $tag
     */
    public function removeTag(\Ibtikar\GlanceDashboardBundle\Document\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tag
     *
     * @return \Doctrine\Common\Collections\Collection $tag
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags
     *
     */
    public function setTags($tags = array())
    {
        return $this->tags = $tags;
    }

    /**
     * Set tagsEn
     *
     */
    public function setTagsEn($tags = array())
    {
        return $this->tagsEn = $tags;
    }

    /**
     * Add tagEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Tag $tagEn
     */
    public function addTagEn(\Ibtikar\GlanceDashboardBundle\Document\Tag $tagEn)
    {
        $this->tagsEn[] = $tagEn;
    }

    /**
     * Remove tagEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Tag $tagEn
     */
    public function removeTagEn(\Ibtikar\GlanceDashboardBundle\Document\Tag $tagEn)
    {
        $this->tagsEn->removeElement($tagEn);
    }

    /**
     * Get tagEn
     *
     * @return \Doctrine\Common\Collections\Collection $tagEn
     */
    public function getTagsEn()
    {
        return $this->tagsEn;
    }

    /**
     * Add tagsEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Tag $tagsEn
     */
    public function addTagsEn(\Ibtikar\GlanceDashboardBundle\Document\Tag $tagsEn)
    {
        $this->tagsEn[] = $tagsEn;
    }

    /**
     * Remove tagsEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Tag $tagsEn
     */
    public function removeTagsEn(\Ibtikar\GlanceDashboardBundle\Document\Tag $tagsEn)
    {
        $this->tagsEn->removeElement($tagsEn);
    }

    /**
     * Set chef
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Staff $chef
     * @return self
     */
    public function setChef(\Ibtikar\GlanceUMSBundle\Document\Staff $chef)
    {
        $this->chef = $chef;
        return $this;
    }

    /**
     * Get chef
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Staff $chef
     */
    public function getChef()
    {
        return $this->chef;
    }

    /**
     * Set preparationTime
     *
     * @param int $preparationTime
     * @return self
     */
    public function setPreparationTime($preparationTime)
    {
        $this->preparationTime = $preparationTime;
        return $this;
    }

    /**
     * Get preparationTime
     *
     * @return int $preparationTime
     */
    public function getPreparationTime()
    {
        return $this->preparationTime;
    }

    /**
     * Set cookingTime
     *
     * @param int $cookingTime
     * @return self
     */
    public function setCookingTime($cookingTime)
    {
        $this->cookingTime = $cookingTime;
        return $this;
    }

    /**
     * Get cookingTime
     *
     * @return int $cookingTime
     */
    public function getCookingTime()
    {
        return $this->cookingTime;
    }

    /**
     * Set difficulty
     *
     * @param int $difficulty
     * @return self
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    /**
     * Get difficulty
     *
     * @return int $difficulty
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * Set servingCount
     *
     * @param int $servingCount
     * @return self
     */
    public function setServingCount($servingCount)
    {
        $this->servingCount = $servingCount;
        return $this;
    }

    /**
     * Get servingCount
     *
     * @return int $servingCount
     */
    public function getServingCount()
    {
        return $this->servingCount;
    }

    /**
     * Add product
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Product $product
     */
    public function addProduct(\Ibtikar\GlanceDashboardBundle\Document\Product $product)
    {
        $this->products[] = $product;
    }

    /**
     * Remove product
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Product $product
     */
    public function removeProduct(\Ibtikar\GlanceDashboardBundle\Document\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection $products
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set course
     *
     * @param int $course
     * @return self
     */
    public function setCourse($course)
    {
        $courese = array();
        foreach ($course as $value) {
            $courese[$value] = $value;
        }
        $this->course = $courese;
        return $this;
    }

    /**
     * Get course
     *
     * @return int $course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set meal
     *
     * @param int $meal
     * @return self
     */
    public function setMeal($meal)
    {
        $meals = array();
        foreach ($meal as $mealType) {
            $meals[$mealType] = $mealType;
        }
        $this->meal = $meals;
        return $this;
    }

    /**
     * Get meal
     *
     * @return int $meal
     */
    public function getMeal()
    {
        return $this->meal;
    }

    /**
     * Set keyIngredient
     *
     * @param int $keyIngredient
     * @return self
     */
    public function setKeyIngredient($keyIngredient)
    {
        $ingrediant = array();
        foreach ($keyIngredient as $value) {
            $ingrediant[$value] = $value;
        }
        $this->keyIngredient = $ingrediant;
        return $this;
    }

    /**
     * Get keyIngredient
     *
     * @return int $keyIngredient
     */
    public function getKeyIngredient()
    {
        return $this->keyIngredient;
    }

    /**
     * Set country
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Country $country
     * @return self
     */
    public function setCountry(\Ibtikar\GlanceUMSBundle\Document\Country $country= NULL)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }



    /**
     * Set assignedTo
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Staff $assignedTo
     * @return self
     */
    public function setAssignedTo(\Ibtikar\GlanceUMSBundle\Document\Staff $assignedTo =NULL)
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

    /**
     * Get assignedTo
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Staff $assignedTo
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    public function getDocumentTranslation()
    {

    }

    public function getSlug()
    {
        return $this->slug;
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
     * Set autoPublishDate
     *
     * @param date $autoPublishDate
     * @return self
     */
    public function setAutoPublishDate($autoPublishDate)
    {
        $this->autoPublishDate = $autoPublishDate;
        return $this;
    }

    /**
     * Get autoPublishDate
     *
     * @return date $autoPublishDate
     */
    public function getAutoPublishDate()
    {
        return $this->autoPublishDate;
    }

    /**
     * Set galleryType
     *
     * @param string $galleryType
     * @return self
     */
    public function setGalleryType($galleryType)
    {
        $this->galleryType = $galleryType;
        return $this;
    }

    /**
     * Get galleryType
     *
     * @return string $galleryType
     */
    public function getGalleryType()
    {
        return $this->galleryType;
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
     * Set reason
     *
     * @param string $reason
     * @return self
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Get reason
     *
     * @return string $reason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set dailysolutionDate
     *
     * @param date $dailysolutionDate
     * @return self
     */
    public function setDailysolutionDate($dailysolutionDate)
    {
        $this->dailysolutionDate = $dailysolutionDate;
        return $this;
    }

    /**
     * Get dailysolutionDate
     *
     * @return date $dailysolutionDate
     */
    public function getDailysolutionDate()
    {
        return $this->dailysolutionDate;
    }

    /**
     * Set noOfViews
     *
     * @param increment $noOfViews
     * @return self
     */
    public function setNoOfViews($noOfViews)
    {
        $this->noOfViews = $noOfViews;
        return $this;
    }

    /**
     * Get noOfViews
     *
     * @return increment $noOfViews
     */
    public function getNoOfViews()
    {
        return $this->noOfViews;
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
     * Get trackingNumber
     *
     * @return string $trackingNumber
     */
    public function getTrackingNumber() {
        return $this->trackingNumber;
    }

    /**
     * Set trackingNumber
     *
     * @param string $trackingNumber
     * @return self
     */
    public function setTrackingNumber($trackingNumber) {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }


    /**
     * Set noOfLikes
     *
     * @param increment $noOfLikes
     * @return self
     */
    public function setNoOfLikes($noOfLikes)
    {
        $this->noOfLikes = $noOfLikes;
        return $this;
    }

    /**
     * Get noOfLikes
     *
     * @return increment $noOfLikes
     */
    public function getNoOfLikes()
    {
        return $this->noOfLikes;
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
     * Add relatedArticle
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedArticle
     */
    public function addRelatedArticle(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedArticle)
    {
        $this->relatedArticle[] = $relatedArticle;
    }

    /**
     * Remove relatedArticle
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedArticle
     */
    public function removeRelatedArticle(\Ibtikar\GlanceDashboardBundle\Document\Recipe $relatedArticle)
    {
        $this->relatedArticle->removeElement($relatedArticle);
    }

    /**
     * Get relatedArticle
     *
     * @return \Doctrine\Common\Collections\Collection $relatedArticle
     */
    public function getRelatedArticle()
    {
        return $this->relatedArticle;
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


    public function setRelatedTip($tip=array())
    {
       $this->relatedTip= $tip;
    }

    public function setRelatedArticle($article=array())
    {
       $this->relatedArticle= $article;
    }

    public function setRelatedRecipe($recipe=array())
    {
       $this->relatedRecipe= $recipe;
    }

    /**
     * Set hideEnglishContent
     *
     * @param boolean $hideEnglishContent
     * @return self
     */
    public function setHideEnglishContent($hideEnglishContent)
    {
        $this->hideEnglishContent = $hideEnglishContent;
        return $this;
    }

    /**
     * Get hideEnglishContent
     *
     * @return boolean $hideEnglishContent
     */
    public function getHideEnglishContent()
    {
        return $this->hideEnglishContent;
    }

    public function getRelatedRecipeJson(){
        $array = array();
        if($this->getRelatedRecipe()){
            foreach($this->getRelatedRecipe() as $recipe){
                $array[] = array(
                            'id'=>$recipe->getId(),
                            'text'=>$recipe->getTitle(),
                            'img' => $this->getDefaultCoverImage($recipe)
                        );
            }

        }
        return $array;
    }

    public function getDefaultCoverImage($document){
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

    public function getRelatedArticleJson(){
        $array = array();
        if($this->getRelatedArticle()){
            foreach($this->getRelatedArticle() as $article){
                $array[] = array(
                            'id'=>$article->getId(),
                            'text'=>$article->getTitle(),
                            'img' => $this->getDefaultCoverImage($article)
                        );
            }

        }
        return $array;
    }

    public function getRelatedTipJson(){
        $array = array();
        if($this->getRelatedTip()){
            foreach($this->getRelatedTip() as $tip){
                $array[] = array(
                            'id'=>$tip->getId(),
                            'text'=>$tip->getTitle(),
                            'img' => $this->getDefaultCoverImage($tip)
                        );
            }

        }
        return $array;
    }

    /**
     * Set migrated
     *
     * @param string $migrated
     * @return self
     */
    public function setMigrated($migrated)
    {
        $this->migrated = $migrated;
        return $this;
    }

    /**
     * Get migrated
     *
     * @return string $migrated
     */
    public function getMigrated()
    {
        return $this->migrated;
    }

    /**
     * Set migrationData
     *
     * @param string $migrationData
     * @return self
     */
    public function setMigrationData($migrationData)
    {
        $this->migrationData = $migrationData;
        return $this;
    }

    /**
     * Get migrationData
     *
     * @return string $migrationData
     */
    public function getMigrationData()
    {
        return $this->migrationData;
    }


    /**
     * Set goodyStar
     *
     * @param boolean $goodyStar
     * @return self
     */
    public function setGoodyStar($goodyStar)
    {
        $this->goodyStar = $goodyStar;
        return $this;
    }

    /**
     * Get goodyStar
     *
     * @return boolean $goodyStar
     */
    public function getGoodyStar()
    {
        return $this->goodyStar;
    }

    /**
     * Add recipeTag
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\RecipeTag $recipeTag
     */
    public function addRecipeTag(\Ibtikar\GlanceDashboardBundle\Document\RecipeTag $recipeTag)
    {
        $this->recipeTags[] = $recipeTag;
    }

    /**
     * Remove recipeTag
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\RecipeTag $recipeTag
     */
    public function removeRecipeTag(\Ibtikar\GlanceDashboardBundle\Document\RecipeTag $recipeTag)
    {
        $this->recipeTags->removeElement($recipeTag);
    }

    /**
     * Get recipeTags
     *
     * @return \Doctrine\Common\Collections\Collection $recipeTags
     */
    public function getRecipeTags()
    {
        return $this->recipeTags;
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
}
