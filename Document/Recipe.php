<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContextInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;
/**
 * @MongoDB\hasLifeCycleCallbacks
 * @MongoDB\Document
 */
class Recipe extends Document {

    public static $difficultyMap = array(
        0 => 'easy',
        1 => 'medium',
        2 => 'difficult'
    );

    public static $courseMap = array(
        0 => 'Salad',
        1 => 'Soup',
        3 => 'Sandwich',
        4 => 'Side Dish',
        5 => 'Main Courses',
        6 => 'Pastry',
        7 => 'Dessert',
        8 => 'Coffee Desserts',
        9 => 'Drink',
    );

    public static $keyIngredientMap = array(
        0 => 'Minced',
        1 => 'Beef',
        2 => 'Lamb',
        3 => 'Chicken',
        4 => 'Seafood',
        5 => 'Vegetable'
    );

    public static $mealMap = array(
        0 => 'Breakfast',
        1 => 'Lunch',
        2 => 'Dinner'
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
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     * )
     */
    private $brief;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
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
     * @MongoDB\String
     */
    private $course;

    /**
     * @MongoDB\String
     */
    private $meal;

    /**
     * @MongoDB\String
     */
    private $keyIngredient;

    /**
     * @MongoDB\Int
     */
    private $servingCount;

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

    public function __construct()
    {
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tagEn = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->title;
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
        $this->course = $course;
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
        $this->meal = $meal;
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
        $this->keyIngredient = $keyIngredient;
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
    public function setCountry(\Ibtikar\GlanceUMSBundle\Document\Country $country)
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
    public function setAssignedTo(\Ibtikar\GlanceUMSBundle\Document\Staff $assignedTo)
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
}
