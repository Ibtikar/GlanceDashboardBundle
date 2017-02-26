<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 */
class QuestionAnswer extends Document  {

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
    private $answer;

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

}
