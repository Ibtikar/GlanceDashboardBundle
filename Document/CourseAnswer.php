<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

//use PUGX\ExtraValidatorBundle\Validator\Constraints as ExtraAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Ibtikar\GlanceDashboardBundle\Validator\Constraints as CustomAssert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceUMSBundle\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @MongoDB\Document
 */
class CourseAnswer extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Course", simple=true)
     */
    private $course;

    /**
     * @MongoDB\String
     */
    private $uid;

    /**
     * @MongoDB\String
     */
    private $locale;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $fullName;


    /**
     * @MongoDB\Float
     */
    private $percentage = 0;




    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\CourseQuestionAnswer" , simple=true)
     */
    private $answers;

    public function __construct() {
        $this->answers = new ArrayCollection();
    }

    public function __toString() {
        return (string) $this->title;
    }


    public function getDocumentTranslation() {
        return 'course';
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
     * Set course
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Course $course
     * @return self
     */
    public function setCourse(\Ibtikar\GlanceDashboardBundle\Document\Course $course)
    {
        $this->course = $course;
        return $this;
    }

    /**
     * Get course
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Course $course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set uid
     *
     * @param string $uid
     * @return self
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get uid
     *
     * @return string $uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return self
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * Get fullName
     *
     * @return string $fullName
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Add answer
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer $answer
     */
    public function addAnswer(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestionAnswer $answer)
    {
        $this->answers[] = $answer;
    }

    /**
     * Remove answer
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer $answer
     */
    public function removeAnswer(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestionAnswer $answer)
    {
        $this->answers->removeElement($answer);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection $answers
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set percentage
     *
     * @param float $percentage
     * @return self
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
        return $this;
    }

    /**
     * Get percentage
     *
     * @return float $percentage
     */
    public function getPercentage()
    {
        return $this->percentage;
    }
}
