<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\HistoryRepository")
 */
class History extends Document {

    static $EDIT = "edit";
    static $DELETE = "delete";
    static $ADD = "add";
    static $ADDRELATED = "add related";
    static $REMOVERELATED = "remove related";

    static $AUTOPUBLISH = "auto-publish";
    static $PUBLISH = "publish";
    static $UNPUBLISH = "unpublish";
    static $ASSIGN = "assign";
    static $DRAFT = "draft";

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(discriminatorField="type", discriminatorMap={"content"="Ibtikar\GlanceDashboardBundle\Document\Recipe", "product"="Ibtikar\GlanceDashboardBundle\Document\Product"})
     */
    private $document;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $action;
    
    /**
     * @MongoDB\String
     */
    private $message;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe", simple=true)
     */
    private $recipe;

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
     * Set document
     *
     * @param $document
     * @return self
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return $document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
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
}
