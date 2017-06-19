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
 */
class Related extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Product" , simple=true)
     */
    private $product;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe" , simple=true)
     */
    private $recipe;

    /**
     * @MongoDB\String
     */
    private $type = 'recipe';


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
     * Set recipe
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $recipe
     * @return self
     */
    public function setRecipe(\Ibtikar\GlanceDashboardBundle\Document\Recipe $recipe)
    {
        $this->recipe = $recipe;
        return $this;
    }

    /**
     * Get recipe
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Recipe $recipe
     */
    public function getRecipe()
    {
        return $this->recipe;
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
}
