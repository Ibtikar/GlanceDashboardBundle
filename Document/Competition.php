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
        'all users' => 'all users',
        'registered users' => 'registered users'
    );
    public static $statuses = array(
        "new" => "new",
        "unpublish" => "unpublish",
        "publish" => "publish",
    );

    public static $coverTypeChoices = array(
        "none" => "none",
        "image" => "image",
        "video" => "video"
    );

    static $COMPETITION_ANSWER_Highlighted_COLORS = array(
        "color1" => "#3F51B5",
        "color2" => "#54D582",
        "color3" => "#53D2BA",
        "color4" => "#00BCD4",
        "color5" => "#F44336",
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
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
sa     */
    private $secondaryTitle;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $secondaryTitleEn;

    /**
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
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 2000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $termsAndConditions;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      max = 2000,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $termsAndConditionsEn;

    /**
     * @Assert\Valid
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Question")
     */
    private $questions;

    /**
     * @Assert\Valid
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Question")
     */
    private $questionsEn;


    /**
     * @Assert\Choice(callback="getValidMediaTypes")
     * @MongoDB\String
     */
    private $coverType = "none";

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $cover;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getValidResultsVisibilities")
     * @MongoDB\String
     */
    private $resultsVisibility;

    /**
     * @MongoDB\Boolean
     */
    protected $goodyStar = false;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getValidAllowedVoters")
     * @MongoDB\String
     */
    private $allowedToVote;

    /**
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
    private $status = "new";

    /**
     * @MongoDB\Int
     */
    private $questionsCount = 0;

    /**
     * @MongoDB\Int
     */
    private $questionsCountEn = 0;

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

    /**
     * @MongoDB\Increment
     */
    private $noOfMale = 0;

    /**
     * @MongoDB\Increment
     */
    private $noOfFemale = 0;

    /**
     * @MongoDB\Increment
     */
    private $noOfRegistered = 0;

    /**
     * @MongoDB\Increment
     */
    private $noOfNonRegistered = 0;

    /**
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\CountryAnswerCount")
     */
    private $countryCount;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slug;

    /**
     * @MongoDB\String
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $slugEn;

    public function __construct() {
        $this->questions = new ArrayCollection();
        $this->questionsEn = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->countryCount = new ArrayCollection();
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
        $this->noOfMale = 0;
        $this->noOfFemale = 0;
        $this->noOfRegistered = 0;
        $this->noOfNonRegistered = 0;
        $this->countryCount = new ArrayCollection();

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
    public static function getValidMediaTypes() {
        return array_keys(static::$coverTypeChoices);
    }

    /**
     * @return array
     */
    public static function getValidAllowedVoters() {
        return array_keys(static::$allowedVoters);
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
        if($expiryDate == ""){
            $this->expiryDate = null;
        }else{
            $this->expiryDate = $expiryDate;
        }

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

    /**
     * Set titleEn
     *
     * @param string $titleEn
     * @return self
     */
    public function setTitleEn($titleEn)
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    /**
     * Get titleEn
     *
     * @return string $titleEn
     */
    public function getTitleEn()
    {
        return $this->titleEn;
    }

    /**
     * Set brief
     *
     * @param string $brief
     * @return self
     */
    public function setBrief($brief)
    {
        $this->brief = $brief;
        return $this;
    }

    /**
     * Get brief
     *
     * @return string $brief
     */
    public function getBrief()
    {
        return $this->brief;
    }

    /**
     * Set briefEn
     *
     * @param string $briefEn
     * @return self
     */
    public function setBriefEn($briefEn)
    {
        $this->briefEn = $briefEn;
        return $this;
    }

    /**
     * Get briefEn
     *
     * @return string $briefEn
     */
    public function getBriefEn()
    {
        return $this->briefEn;
    }

    /**
     * Set secondaryTitle
     *
     * @param string $secondaryTitle
     * @return self
     */
    public function setSecondaryTitle($secondaryTitle)
    {
        $this->secondaryTitle = $secondaryTitle;
        return $this;
    }

    /**
     * Get secondaryTitle
     *
     * @return string $secondaryTitle
     */
    public function getSecondaryTitle()
    {
        return $this->secondaryTitle;
    }

    /**
     * Set secondaryTitleEn
     *
     * @param string $secondaryTitleEn
     * @return self
     */
    public function setSecondaryTitleEn($secondaryTitleEn)
    {
        $this->secondaryTitleEn = $secondaryTitleEn;
        return $this;
    }

    /**
     * Get secondaryTitleEn
     *
     * @return string $secondaryTitleEn
     */
    public function getSecondaryTitleEn()
    {
        return $this->secondaryTitleEn;
    }

    /**
     * Add questionsEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Question $questionsEn
     */
    public function addQuestionsEn(\Ibtikar\GlanceDashboardBundle\Document\Question $questionsEn)
    {
        $this->questionsEn[] = $questionsEn;
    }

    /**
     * Remove questionsEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Question $questionsEn
     */
    public function removeQuestionsEn(\Ibtikar\GlanceDashboardBundle\Document\Question $questionsEn)
    {
        $this->questionsEn->removeElement($questionsEn);
    }

    /**
     * Get questionsEn
     *
     * @return \Doctrine\Common\Collections\Collection $questionsEn
     */
    public function getQuestionsEn()
    {
        return $this->questionsEn;
    }

    /**
     * Set termsAndConditions
     *
     * @param string $termsAndConditions
     * @return self
     */
    public function setTermsAndConditions($termsAndConditions)
    {
        $this->termsAndConditions = $termsAndConditions;
        return $this;
    }

    /**
     * Get termsAndConditions
     *
     * @return string $termsAndConditions
     */
    public function getTermsAndConditions()
    {
        return $this->termsAndConditions;
    }

    /**
     * Set termsAndConditionsEn
     *
     * @param string $termsAndConditionsEn
     * @return self
     */
    public function setTermsAndConditionsEn($termsAndConditionsEn)
    {
        $this->termsAndConditionsEn = $termsAndConditionsEn;
        return $this;
    }

    /**
     * Get termsAndConditionsEn
     *
     * @return string $termsAndConditionsEn
     */
    public function getTermsAndConditionsEn()
    {
        return $this->termsAndConditionsEn;
    }



    /**
     * Set coverType
     *
     * @param string $coverType
     * @return self
     */
    public function setCoverType($coverType)
    {
        $this->coverType = $coverType;
        return $this;
    }

    /**
     * Get coverType
     *
     * @return string $coverType
     */
    public function getCoverType()
    {
        return $this->coverType;
    }

    /**
     * Set cover
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Media $cover
     * @return self
     */
    public function setCover(\Ibtikar\GlanceDashboardBundle\Document\Media $cover)
    {
        $this->cover = $cover;
        return $this;
    }

    /**
     * Get cover
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Media $cover
     */
    public function getCover()
    {
        return $this->cover;
    }

        /**
     * Set goodyStar
     *
     * @param boolean $goodyStar
     * @return self
     */
    public function setGoodyStar($goodyStar)
    {
        $this->goodyStar = $goodyStar;
        return $this;
    }

    /**
     * Get goodyStar
     *
     * @return boolean $goodyStar
     */
    public function getGoodyStar()
    {
        return $this->goodyStar;
    }

    /**
     * Set questionsCountEn
     *
     * @param int $questionsCountEn
     * @return self
     */
    public function setQuestionsCountEn($questionsCountEn)
    {
        $this->questionsCountEn = $questionsCountEn;
        return $this;
    }

    /**
     * Get questionsCountEn
     *
     * @return int $questionsCountEn
     */
    public function getQuestionsCountEn()
    {
        return $this->questionsCountEn;
    }

    /**
     * Set noOfMale
     *
     * @param increment $noOfMale
     * @return self
     */
    public function setNoOfMale($noOfMale)
    {
        $this->noOfMale = $noOfMale;
        return $this;
    }

    /**
     * Get noOfMale
     *
     * @return increment $noOfMale
     */
    public function getNoOfMale()
    {
        return $this->noOfMale;
    }

    /**
     * Set noOfFemale
     *
     * @param increment $noOfFemale
     * @return self
     */
    public function setNoOfFemale($noOfFemale)
    {
        $this->noOfFemale = $noOfFemale;
        return $this;
    }

    /**
     * Get noOfFemale
     *
     * @return increment $noOfFemale
     */
    public function getNoOfFemale()
    {
        return $this->noOfFemale;
    }

    /**
     * Set noOfRegistered
     *
     * @param increment $noOfRegistered
     * @return self
     */
    public function setNoOfRegistered($noOfRegistered)
    {
        $this->noOfRegistered = $noOfRegistered;
        return $this;
    }

    /**
     * Get noOfRegistered
     *
     * @return increment $noOfRegistered
     */
    public function getNoOfRegistered()
    {
        return $this->noOfRegistered;
    }

    /**
     * Set noOfNonRegistered
     *
     * @param increment $noOfNonRegistered
     * @return self
     */
    public function setNoOfNonRegistered($noOfNonRegistered)
    {
        $this->noOfNonRegistered = $noOfNonRegistered;
        return $this;
    }

    /**
     * Get noOfNonRegistered
     *
     * @return increment $noOfNonRegistered
     */
    public function getNoOfNonRegistered()
    {
        return $this->noOfNonRegistered;
    }

    /**
     * Add countryCount
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CountryAnswerCount $countryCount
     */
    public function addCountryCount(\Ibtikar\GlanceDashboardBundle\Document\CountryAnswerCount $countryCount)
    {
        $this->countryCount[] = $countryCount;
    }

    /**
     * Remove countryCount
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CountryAnswerCount $countryCount
     */
    public function removeCountryCount(\Ibtikar\GlanceDashboardBundle\Document\CountryAnswerCount $countryCount)
    {
        $this->countryCount->removeElement($countryCount);
    }

    /**
     * Get countryCount
     *
     * @return \Doctrine\Common\Collections\Collection $countryCount
     */
    public function getCountryCount()
    {
        return $this->countryCount;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Set slugEn
     *
     * @param string $slugEn
     * @return self
     */
    public function setSlugEn($slugEn)
    {
        $this->slugEn = $slugEn;
        return $this;
    }

    /**
     * Get slugEn
     *
     * @return string $slugEn
     */
    public function getSlugEn()
    {
        return $this->slugEn;
    }
}
