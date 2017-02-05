<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Publishable;

/**
 * @MongoDB\hasLifeCycleCallbacks
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\MagazineRepository")
 */
class Message extends Document
{
    public static $statuses = array(
        "new" => "new",
        "inprogress" => "inprogress",
        "close" => "close",
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
     * @MongoDB\String
     */
    private $content;
    
    
    /**
     * @MongoDB\String
     */
    private $type;
    
    
    /**
     * @MongoDB\String
     */
    private $tracking;
    
    
    /**
     * @MongoDB\String
     */
    private $status = 'new';
    
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return (string) $this->name;
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
     * Set user
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Media $coverPhoto
     * @return self
     */
    public function setUser(\Ibtikar\GlanceUMSBundle\Document\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Media $coverPhoto
     */
    public function getUser()
    {
        return $this->user;
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
     * Set content
     *
     * @param string $content
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
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
     * Get tracking
     *
     * @return string $tracking
     */
    public function getTracking()
    {
        return $this->tracking;
    }
    
    /**
     * Set tracking
     *
     * @param string $tracking
     * @return self
     */
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;
        return $this;
    }
}
