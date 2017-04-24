<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\ExportRepository")
 * @MongoDBUnique(fields="name")
 * @MongoDB\HasLifecycleCallbacks
 */
class Export extends Document {

    const READY       = "0";
    const IN_PROGRESS = "1";
    const FINISHED    = "2";

    const VISITORS          = "1";
    const CONTACT           = "2";
    const CONTACTGROUP      = "3";

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $type;


    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $extension = 'xls';

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $state;

    /**
     * @MongoDB\Hash
     */
    protected $params = array();

    /**
     * @MongoDB\Hash
     */
    protected $fields = array();


    public function __toString() {
        return $this->name;
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
     * Set state
     *
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return string $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set params
     *
     * @param hash $params
     * @return self
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get params
     *
     * @return hash $params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set fields
     *
     * @param hash $fields
     * @return self
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Get fields
     *
     * @return hash $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set extension
     *
     * @param string $extension
     * @return self
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Get extension
     *
     * @return string $extension
     */
    public function getExtension()
    {
        return $this->extension;
    }

}
