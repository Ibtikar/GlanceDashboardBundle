<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceUMSBundle\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MongoDB\Document
 */
class MetaTagTempelate extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $recipeMetaTagTitleAr;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $recipeMetaTagDescriptionAr;
    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $recipeMetaTagTitleEn;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $recipeMetaTagDescriptionEn;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $articleMetaTagTitleAr;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $articleMetaTagdecriptionAr;
    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $articleMetaTagTitleEn;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $articleMetaTagdecriptionEn;

    /**
     * @MongoDB\String
     */
    private $shortName = 'recipe';

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

   

    /**
     * Set recipeMetaTagTitleAr
     *
     * @param string $recipeMetaTagTitleAr
     * @return self
     */
    public function setRecipeMetaTagTitleAr($recipeMetaTagTitleAr)
    {
        $this->recipeMetaTagTitleAr = $recipeMetaTagTitleAr;
        return $this;
    }

    /**
     * Get recipeMetaTagTitleAr
     *
     * @return string $recipeMetaTagTitleAr
     */
    public function getRecipeMetaTagTitleAr()
    {
        return $this->recipeMetaTagTitleAr;
    }

    /**
     * Set recipeMetaTagDescriptionAr
     *
     * @param string $recipeMetaTagDescriptionAr
     * @return self
     */
    public function setRecipeMetaTagDescriptionAr($recipeMetaTagDescriptionAr)
    {
        $this->recipeMetaTagDescriptionAr = $recipeMetaTagDescriptionAr;
        return $this;
    }

    /**
     * Get recipeMetaTagDescriptionAr
     *
     * @return string $recipeMetaTagDescriptionAr
     */
    public function getRecipeMetaTagDescriptionAr()
    {
        return $this->recipeMetaTagDescriptionAr;
    }

    /**
     * Set recipeMetaTagTitleEn
     *
     * @param string $recipeMetaTagTitleEn
     * @return self
     */
    public function setRecipeMetaTagTitleEn($recipeMetaTagTitleEn)
    {
        $this->recipeMetaTagTitleEn = $recipeMetaTagTitleEn;
        return $this;
    }

    /**
     * Get recipeMetaTagTitleEn
     *
     * @return string $recipeMetaTagTitleEn
     */
    public function getRecipeMetaTagTitleEn()
    {
        return $this->recipeMetaTagTitleEn;
    }

    /**
     * Set recipeMetaTagDescriptionEn
     *
     * @param string $recipeMetaTagDescriptionEn
     * @return self
     */
    public function setRecipeMetaTagDescriptionEn($recipeMetaTagDescriptionEn)
    {
        $this->recipeMetaTagDescriptionEn = $recipeMetaTagDescriptionEn;
        return $this;
    }

    /**
     * Get recipeMetaTagDescriptionEn
     *
     * @return string $recipeMetaTagDescriptionEn
     */
    public function getRecipeMetaTagDescriptionEn()
    {
        return $this->recipeMetaTagDescriptionEn;
    }

    /**
     * Set articleMetaTagTitleAr
     *
     * @param string $articleMetaTagTitleAr
     * @return self
     */
    public function setArticleMetaTagTitleAr($articleMetaTagTitleAr)
    {
        $this->articleMetaTagTitleAr = $articleMetaTagTitleAr;
        return $this;
    }

    /**
     * Get articleMetaTagTitleAr
     *
     * @return string $articleMetaTagTitleAr
     */
    public function getArticleMetaTagTitleAr()
    {
        return $this->articleMetaTagTitleAr;
    }

    /**
     * Set articleMetaTagdecriptionAr
     *
     * @param string $articleMetaTagdecriptionAr
     * @return self
     */
    public function setArticleMetaTagdecriptionAr($articleMetaTagdecriptionAr)
    {
        $this->articleMetaTagdecriptionAr = $articleMetaTagdecriptionAr;
        return $this;
    }

    /**
     * Get articleMetaTagdecriptionAr
     *
     * @return string $articleMetaTagdecriptionAr
     */
    public function getArticleMetaTagdecriptionAr()
    {
        return $this->articleMetaTagdecriptionAr;
    }

    /**
     * Set articleMetaTagTitleEn
     *
     * @param string $articleMetaTagTitleEn
     * @return self
     */
    public function setArticleMetaTagTitleEn($articleMetaTagTitleEn)
    {
        $this->articleMetaTagTitleEn = $articleMetaTagTitleEn;
        return $this;
    }

    /**
     * Get articleMetaTagTitleEn
     *
     * @return string $articleMetaTagTitleEn
     */
    public function getArticleMetaTagTitleEn()
    {
        return $this->articleMetaTagTitleEn;
    }

    /**
     * Set articleMetaTagdecriptionEn
     *
     * @param string $articleMetaTagdecriptionEn
     * @return self
     */
    public function setArticleMetaTagdecriptionEn($articleMetaTagdecriptionEn)
    {
        $this->articleMetaTagdecriptionEn = $articleMetaTagdecriptionEn;
        return $this;
    }

    /**
     * Get articleMetaTagdecriptionEn
     *
     * @return string $articleMetaTagdecriptionEn
     */
    public function getArticleMetaTagdecriptionEn()
    {
        return $this->articleMetaTagdecriptionEn;
    }

    /**
     * Set shortName
     *
     * @param string $shortName
     * @return self
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * Get shortName
     *
     * @return string $shortName
     */
    public function getShortName()
    {
        return $this->shortName;
    }
}
