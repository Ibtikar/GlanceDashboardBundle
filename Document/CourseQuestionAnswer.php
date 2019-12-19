<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 */
class CourseQuestionAnswer extends Document  {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Question", simple=true)
     */
    private $question;

    /**
     * @MongoDB\String
     */
    private $type;

    /**
     * @MongoDB\String
     */
    private $answer;

    /**
     * @MongoDB\String
     */
    private $correctAnswer=false;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set question
     *
     * @param string $question
     * @return self
     */
    public function setQuestion($question) {
        $this->question = $question;
        return $this;
    }

    /**
     * Get question
     *
     * @return string $question
     */
    public function getQuestion() {
        return $this->question;
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
     * Set correctAnswer
     *
     * @param string $correctAnswer
     * @return self
     */
    public function setCorrectAnswer($correctAnswer)
    {
        $this->correctAnswer = $correctAnswer;
        return $this;
    }

    /**
     * Get correctAnswer
     *
     * @return string $correctAnswer
     */
    public function getCorrectAnswer()
    {
        return $this->correctAnswer;
    }
}
