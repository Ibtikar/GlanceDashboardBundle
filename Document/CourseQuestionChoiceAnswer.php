<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class CourseQuestionChoiceAnswer {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(min=2)
     */
    private $answer;

    /**
     * @MongoDB\Boolean
     */
    private $correctAnswer = false;

    /**
     * @MongoDB\Increment
     */
    private $selectionCount = 0;

    /**
     * @MongoDB\Float
     */
    private $percentage = 0;

    public function __clone() {
        $this->id = null;
        $this->selectionCount = 0;
        $this->percentage = 0;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set answer
     *
     * @param string $answer
     * @return self
     */
    public function setAnswer($answer) {
        $this->answer = $answer;
        return $this;
    }

    /**
     * Get answer
     *
     * @return string $answer
     */
    public function getAnswer() {
        return $this->answer;
    }

    /**
     * Set selectionCount
     *
     * @param increment $selectionCount
     * @return self
     */
    public function setSelectionCount($selectionCount) {
        $this->selectionCount = $selectionCount;
        return $this;
    }

    /**
     * Get selectionCount
     *
     * @return increment $selectionCount
     */
    public function getSelectionCount() {
        return $this->selectionCount;
    }

    /**
     * Set $percentage
     *
     * @param float $percentage
     * @return self
     */
    public function setPercentage($percentage) {
        $this->percentage = $percentage;
        return $this;
    }

    /**
     * Get $percentage
     *
     * @return float percentage
     */
    public function getPercentage() {
        return $this->percentage;
    }

    /**
     * Set correctAnswer
     *
     * @param boolean $correctAnswer
     * @return self
     */
    public function setCorrectAnswer($correctAnswer) {
        $this->correctAnswer = $correctAnswer;
        return $this;
    }

    /**
     * Get correctAnswer
     *
     * @return boolean $correctAnswer
     */
    public function getCorrectAnswer() {
        return $this->correctAnswer;
    }

}
