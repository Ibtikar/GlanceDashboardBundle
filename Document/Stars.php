<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 */
class Stars extends Document
{

    public static $statuses = array(
        "new" => "new",
        "approved" => "approved",
        "rejected" => "rejected",
    );

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $qualities;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $howTo;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $meaning;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $famousDish;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $mobile;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $city;

    /**
     * @Assert\NotBlank
     * @MongoDB\Date
     */
    protected $birthDate;

    /**
     * @Assert\NotBlank
     * @MongoDB\Boolean
     */
    private $married;

    /**
     * @Assert\NotBlank
     * @MongoDB\Boolean
     */
    private $children;

    /**
     * @Assert\NotBlank
     * @MongoDB\Boolean
     */
    private $employee;

    /**
     * @MongoDB\String
     */
    private $status = 'new';

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\User", discriminatorField="type", discriminatorMap={"visitor"="Ibtikar\GlanceUMSBundle\Document\Visitor", "staff"="Ibtikar\GlanceUMSBundle\Document\Staff"})
     */
    private $user;

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
     * Set qualities
     *
     * @param string $qualities
     * @return self
     */
    public function setQualities($qualities)
    {
        $this->qualities = $qualities;
        return $this;
    }

    /**
     * Get qualities
     *
     * @return string $qualities
     */
    public function getQualities()
    {
        return $this->qualities;
    }

    /**
     * Set howTo
     *
     * @param string $howTo
     * @return self
     */
    public function setHowTo($howTo)
    {
        $this->howTo = $howTo;
        return $this;
    }

    /**
     * Get howTo
     *
     * @return string $howTo
     */
    public function getHowTo()
    {
        return $this->howTo;
    }

    /**
     * Set meaning
     *
     * @param string $meaning
     * @return self
     */
    public function setMeaning($meaning)
    {
        $this->meaning = $meaning;
        return $this;
    }

    /**
     * Get meaning
     *
     * @return string $meaning
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * Set famousDish
     *
     * @param string $famousDish
     * @return self
     */
    public function setFamousDish($famousDish)
    {
        $this->famousDish = $famousDish;
        return $this;
    }

    /**
     * Get famousDish
     *
     * @return string $famousDish
     */
    public function getFamousDish()
    {
        return $this->famousDish;
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
     * Set mobile
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Phone $mobile
     * @return self
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * Get mobile
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Phone $mobile
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set birthDate
     *
     * @param date $birthDate
     * @return self
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * Get birthDate
     *
     * @return date $birthDate
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set married
     *
     * @param boolean $married
     * @return self
     */
    public function setMarried($married)
    {
        $this->married = $married;
        return $this;
    }

    /**
     * Get married
     *
     * @return boolean $married
     */
    public function getMarried()
    {
        return $this->married;
    }

    /**
     * Set children
     *
     * @param boolean $children
     * @return self
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get children
     *
     * @return boolean $children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set employee
     *
     * @param boolean $employee
     * @return self
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;
        return $this;
    }

    /**
     * Get employee
     *
     * @return boolean $employee
     */
    public function getEmployee()
    {
        return $this->employee;
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
     * @param date $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return date $user
     */
    public function getUser()
    {
        return $this->user;
    }
}
