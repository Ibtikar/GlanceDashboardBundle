<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @MongoDB\EmbeddedDocument
 */
class CourseQuestion {

    public static $questionTypes = array(
        'single-answer' => 'single answer',
    );

    public static $answerDisplayType = array(
        'vertical' => 'vertical',
    );

    public static $resultDisplayType = array(
        'public' => 'public',
        'private' => 'private'
    );

    public static $answerImportanceType = array(
        'mandatory' => 'mandatory',
    );

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\CourseQuestionChoiceAnswer")
     */
    private $answers;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $question;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $answerDisplay ="vertical";

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $resultDisplay ="public";

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $answerImportance ="mandatory";

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $questionType;

    /**
     * @MongoDB\Int
     */
    private $sumAnswers = 0;

    /**
     * @MongoDB\Increment
     */
    private $totalAnswersVote = 0;

    public function __construct() {
        $this->answers = new ArrayCollection();
        $this->questionType = key(static::$questionTypes);

    }

    public function __clone() {
        $this->createdAt = new \DateTime();
        $this->id = null;
        $tempAnswers = $this->answers;
        $this->answers = new ArrayCollection();

        if(in_array($this->questionType, array('single-answer','multiple-answer','single answer','multiple answer'))){
            foreach ($tempAnswers as $answers) {
                $this->answers[] = clone $answers;
            }
        }
    }

    public function __toString() {
        return (string) $this->question;
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
     * Add answer
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CourseQuestionChoiceAnswer $answer
     */
    public function addAnswer(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestionChoiceAnswer $answer)
    {
        $this->answers[] = $answer;
    }

    /**
     * Remove answer
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CourseQuestionChoiceAnswer $answer
     */
    public function removeAnswer(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestionChoiceAnswer $answer)
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
     * Set question
     *
     * @param string $question
     * @return self
     */
    public function setQuestion($question)
    {
        $this->question = $question;
        return $this;
    }

    /**
     * Get question
     *
     * @return string $question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answerDisplay
     *
     * @param string $answerDisplay
     * @return self
     */
    public function setAnswerDisplay($answerDisplay)
    {
        $this->answerDisplay = $answerDisplay;
        return $this;
    }

    /**
     * Get answerDisplay
     *
     * @return string $answerDisplay
     */
    public function getAnswerDisplay()
    {
        return $this->answerDisplay;
    }

    /**
     * Set resultDisplay
     *
     * @param string $resultDisplay
     * @return self
     */
    public function setResultDisplay($resultDisplay)
    {
        $this->resultDisplay = $resultDisplay;
        return $this;
    }

    /**
     * Get resultDisplay
     *
     * @return string $resultDisplay
     */
    public function getResultDisplay()
    {
        return $this->resultDisplay;
    }

    /**
     * Set answerImportance
     *
     * @param string $answerImportance
     * @return self
     */
    public function setAnswerImportance($answerImportance)
    {
        $this->answerImportance = $answerImportance;
        return $this;
    }

    /**
     * Get answerImportance
     *
     * @return string $answerImportance
     */
    public function getAnswerImportance()
    {
        return $this->answerImportance;
    }

    /**
     * Set questionType
     *
     * @param string $questionType
     * @return self
     */
    public function setQuestionType($questionType)
    {
        $this->questionType = $questionType;
        return $this;
    }

    /**
     * Get questionType
     *
     * @return string $questionType
     */
    public function getQuestionType()
    {
        return $this->questionType;
    }

    /**
     * Set sumAnswers
     *
     * @param int $sumAnswers
     * @return self
     */
    public function setSumAnswers($sumAnswers)
    {
        $this->sumAnswers = $sumAnswers;
        return $this;
    }

    /**
     * Get sumAnswers
     *
     * @return int $sumAnswers
     */
    public function getSumAnswers()
    {
        return $this->sumAnswers;
    }

    /**
     * Set totalAnswersVote
     *
     * @param increment $totalAnswersVote
     * @return self
     */
    public function setTotalAnswersVote($totalAnswersVote)
    {
        $this->totalAnswersVote = $totalAnswersVote;
        return $this;
    }

    /**
     * Get totalAnswersVote
     *
     * @return increment $totalAnswersVote
     */
    public function getTotalAnswersVote()
    {
        return $this->totalAnswersVote;
    }
}
