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
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\CompetitionRepository")
 * @CustomAssert\ExpireBeforePublish
 */
class Competition extends Publishable {

    public static $resultsVisibilities = array(
        'visitors' => 'visitors',
        'users' => 'registered users',
        'voters' => 'voters',
        'nobody' => 'nobody',
    );
    public static $allowedVoters = array(
        'visitors' => 'visitors',
        'registered-users' => 'registered users'
    );
    public static $statuses = array(
        "new" => "new",
        "autopublish" => "autopublish",
        "unpublished" => "unpublished",
        "published" => "published",
        'deleted'=>'deleted'
    );

    static $COMPETITION_ANSWER_Highlighted_COLORS = array(
        "color1" => "#3498db",
        "color2" => "#e74c3c",
        "color3" => "#1abc9c",
        "color4" => "#34495e",
        "color5" => "#f1c40f",
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
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $titleEn;

    /**
     * @Assert\NotBlank

     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $brief;

    /**
     * @Assert\NotBlank

     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 1000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $briefEn;

    /**
     * @Assert\Valid
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Question")
     */
    private $questions;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getValidResultsVisibilities")
     * @MongoDB\String
     */
    private $resultsVisibility;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getValidAllowedVoters")
     * @MongoDB\String
     */
    private $allowedToVote;

    /**
     * @Assert\Date
     * @ExtraAssert\MinDate(limit="now")
     * @MongoDB\Date
     */
    private $expiryDate;

    /**
     * @MongoDB\Boolean
     */
    private $answersEnabled = true;

    /**
     * @MongoDB\String
     */
    private $status;

    /**
     * @MongoDB\Int
     */
    private $questionsCount = 0;

    /**
     * @MongoDB\ReferenceMany()
     */
    private $participants;

    /**
     * @MongoDB\Increment
     */
    private $noOfLikes = 0;

    /**
     * @MongoDB\Increment
     */
    private $noOfShares = 0;

    /**
     * @MongoDB\Date
     */
    private $publishedAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Staff", simple=true)
     */
    private $publishedBy;

    /**
     * @MongoDB\Date
     */
    private $autoPublishDate;

    /**
     * @MongoDB\Increment
     */
    private $noOfViews = 0;

    /**
     * @MongoDB\Increment
     */
    private $noOfAnswer = 0;

    public function __construct() {
        $this->questions = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->status = static::$statuses['new'];
        $this->resultsVisibility = key(static::$resultsVisibilities);
        $this->allowedToVote = key(static::$allowedVoters);
    }

    public function __toString() {
        return (string) $this->title;
    }


    public function __clone() {
        $this->id = NULL;
        $this->noOfLikes = 0;
        $this->noOfShares = 0;
        $this->noOfAnswer = 0;
        $this->noOfViews = 0;

        $tempQuestions = $this->questions;

        $this->questions = new ArrayCollection();
        $this->participants = new ArrayCollection();

        foreach ($tempQuestions as $question) {
            $this->questions[] = clone $question;
        }
    }

    public function getDocumentTranslation() {
        return 'competition';
    }

    /**
     * @return array
     */
    public static function getValidQuestionTypes() {
        return array_keys(static::$questionTypes);
    }

    /**
     * @return array
     */
    public static function getValidResultsVisibilities() {
        return array_keys(static::$resultsVisibilities);
    }

    /**
     * @return array
     */
    public static function getValidAllowedVoters() {
        return array_keys(static::$allowedVoters);
    }


    public function getSlug() {

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
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Add question
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Question $question
     */
    public function addQuestion(\Ibtikar\GlanceDashboardBundle\Document\Question $question)
    {
        $this->questions[] = $question;
    }

    /**
     * Remove question
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Question $question
     */
    public function removeQuestion(\Ibtikar\GlanceDashboardBundle\Document\Question $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection $questions
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Add participant
     *
     * @param $participant
     */
    public function addParticipant($participant)
    {
        $this->participants[] = $participant;
    }

    /**
     * Remove participant
     *
     * @param  $participant
     */
    public function removeParticipant($participant)
    {
        $this->participants->removeElement($participant);
    }

    /**
     * Get participant
     *
     * @return \Doctrine\Common\Collections\Collection $participants
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Set resultsVisibility
     *
     * @param string $resultsVisibility
     * @return self
     */
    public function setResultsVisibility($resultsVisibility)
    {
        $this->resultsVisibility = $resultsVisibility;
        return $this;
    }

    /**
     * Get resultsVisibility
     *
     * @return string $resultsVisibility
     */
    public function getResultsVisibility()
    {
        return $this->resultsVisibility;
    }

    /**
     * Set allowedToVote
     *
     * @param string $allowedToVote
     * @return self
     */
    public function setAllowedToVote($allowedToVote)
    {
        $this->allowedToVote = $allowedToVote;
        return $this;
    }

    /**
     * Get allowedToVote
     *
     * @return string $allowedToVote
     */
    public function getAllowedToVote()
    {
        return $this->allowedToVote;
    }

    /**
     * Set expiryDate
     *
     * @param date $expiryDate
     * @return self
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return date $expiryDate
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set answersEnabled
     *
     * @param boolean $answersEnabled
     * @return self
     */
    public function setAnswersEnabled($answersEnabled)
    {
        $this->answersEnabled = $answersEnabled;
        return $this;
    }

    /**
     * Get answersEnabled
     *
     * @return boolean $answersEnabled
     */
    public function getAnswersEnabled()
    {
        return $this->answersEnabled;
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
     * Set questionsCount
     *
     * @param int $questionsCount
     * @return self
     */
    public function setQuestionsCount($questionsCount)
    {
        $this->questionsCount = $questionsCount;
        return $this;
    }

    /**
     * Get questionsCount
     *
     * @return int $questionsCount
     */
    public function getQuestionsCount()
    {
        return $this->questionsCount;
    }

    /**
     * Set noOfLikes
     *
     * @param increment $noOfLikes
     * @return self
     */
    public function setNoOfLikes($noOfLikes)
    {
        $this->noOfLikes = $noOfLikes;
        return $this;
    }

    /**
     * Get noOfLikes
     *
     * @return increment $noOfLikes
     */
    public function getNoOfLikes()
    {
        return $this->noOfLikes;
    }

    /**
     * Set noOfShares
     *
     * @param increment $noOfShares
     * @return self
     */
    public function setNoOfShares($noOfShares)
    {
        $this->noOfShares = $noOfShares;
        return $this;
    }

    /**
     * Get noOfShares
     *
     * @return increment $noOfShares
     */
    public function getNoOfShares()
    {
        return $this->noOfShares;
    }

    /**
     * Set publishedAt
     *
     * @param date $publishedAt
     * @return self
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return date $publishedAt
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set publishedBy
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy
     * @return self
     */
    public function setPublishedBy(\Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy)
    {
        $this->publishedBy = $publishedBy;
        return $this;
    }

    /**
     * Get publishedBy
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Staff $publishedBy
     */
    public function getPublishedBy()
    {
        return $this->publishedBy;
    }

    /**
     * Set autoPublishDate
     *
     * @param date $autoPublishDate
     * @return self
     */
    public function setAutoPublishDate($autoPublishDate)
    {
        $this->autoPublishDate = $autoPublishDate;
        return $this;
    }

    /**
     * Get autoPublishDate
     *
     * @return date $autoPublishDate
     */
    public function getAutoPublishDate()
    {
        return $this->autoPublishDate;
    }

    public function delete(DocumentManager $dm, User $user = null,  $container = null, $deleteOption = null)
    {
        if ($container) {
            $this->status = Competition::$statuses['deleted'];
        }
        if ($user) {
            $this->deletedBy = $user;
        }
        $this->autoPublishDate= null;
        $this->deletedAt = new \DateTime();
    }

    public function getPublish() {
        if($this->status == self::$statuses["published"]){
            return true;
        }
        return false;
    }





    /**
     * Set noOfViews
     *
     * @param increment $noOfViews
     * @return self
     */
    public function setNoOfViews($noOfViews)
    {
        $this->noOfViews = $noOfViews;
        return $this;
    }

    /**
     * Get noOfViews
     *
     * @return increment $noOfViews
     */
    public function getNoOfViews()
    {
        return $this->noOfViews;
    }

    /**
     * Set noOfAnswer
     *
     * @param increment $noOfAnswer
     * @return self
     */
    public function setNoOfAnswer($noOfAnswer)
    {
        $this->noOfAnswer = $noOfAnswer;
        return $this;
    }

    /**
     * Get noOfAnswer
     *
     * @return increment $noOfAnswer
     */
    public function getNoOfAnswer()
    {
        return $this->noOfAnswer;
    }

    public function setPublish($status) {
        $this->setStatus($status ? self::$statuses["published"] : self::$statuses["unpublished"]);
        return $this;
    }
}