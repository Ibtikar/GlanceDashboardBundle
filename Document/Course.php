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
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\CourseRepository")
 */
class Course extends Publishable {


    public static $allowedVoters = array(
        'all users' => 'all users',
        'registered users' => 'registered users'
    );
    public static $statuses = array(
        "new" => "new",
        "unpublish" => "unpublish",
        "publish" => "publish",
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
    private $name;

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
    private $nameEn;





    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Url
     */
    private $youtubeChannel;



    /**
     * @Assert\Valid
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\CourseQuestion")
     */
    private $questions;

    /**
     * @Assert\Valid
     * @MongoDB\EmbedMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\CourseQuestion")
     */
    private $questionsEn;



    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Media", simple=true)
     */
    private $cover;

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
    }

    public function __toString() {
        return (string) $this->name;
    }


    public function __clone() {
        $this->id = NULL;
        $this->noOfLikes = 0;
        $this->noOfShares = 0;
        $this->noOfAnswer = 0;
        $this->noOfViews = 0;
        $this->noOfMale = 0;
        $this->noOfFemale = 0;

        $this->countryCount = new ArrayCollection();

        $tempQuestions = $this->questions;

        $this->questions = new ArrayCollection();
        $this->participants = new ArrayCollection();

        foreach ($tempQuestions as $question) {
            $this->questions[] = clone $question;
        }
    }

    public function getDocumentTranslation() {
        return 'course';
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
        return $this->slug;
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
     * Set nameEn
     *
     * @param string $nameEn
     * @return self
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
        return $this;
    }

    /**
     * Get nameEn
     *
     * @return string $nameEn
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * Set youtubeChannel
     *
     * @param string $youtubeChannel
     * @return self
     */
    public function setYoutubeChannel($youtubeChannel)
    {
        $this->youtubeChannel = $youtubeChannel;
        return $this;
    }

    /**
     * Get youtubeChannel
     *
     * @return string $youtubeChannel
     */
    public function getYoutubeChannel()
    {
        return $this->youtubeChannel;
    }

    /**
     * Add question
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $question
     */
    public function addQuestion(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $question)
    {
        $this->questions[] = $question;
    }

    /**
     * Remove question
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $question
     */
    public function removeQuestion(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $question)
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
     * Add questionsEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $questionsEn
     */
    public function addQuestionsEn(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $questionsEn)
    {
        $this->questionsEn[] = $questionsEn;
    }

    /**
     * Remove questionsEn
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $questionsEn
     */
    public function removeQuestionsEn(\Ibtikar\GlanceDashboardBundle\Document\CourseQuestion $questionsEn)
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
     * @param $participant
     */
    public function removeParticipant($participant)
    {
        $this->participants->removeElement($participant);
    }

    /**
     * Get participants
     *
     * @return \Doctrine\Common\Collections\Collection $participants
     */
    public function getParticipants()
    {
        return $this->participants;
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
