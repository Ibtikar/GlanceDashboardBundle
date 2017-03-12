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
class CompetitionAnswer extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Competition", simple=true)
     */
    private $competition;

    /**
     * @MongoDB\String
     */
    private $uid;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $fullName;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $email;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $phone;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Country", simple=true)
     */
    protected $country;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $city;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $gender;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer" , simple=true)
     */
    private $answers;

    public function __construct() {
        $this->answers = new ArrayCollection();
    }

    public function __toString() {
        return (string) $this->title;
    }


    public function getDocumentTranslation() {
        return 'competition';
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
     * Set competition
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Competition $competition
     * @return self
     */
    public function setCompetition(\Ibtikar\GlanceDashboardBundle\Document\Competition $competition)
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * Get competition
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Competition $competition
     */
    public function getCompetition()
    {
        return $this->competition;
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
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set country
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Country $country
     * @return self
     */
    public function setCountry(\Ibtikar\GlanceUMSBundle\Document\Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Country $country
     */
    public function getCountry()
    {
        return $this->country;
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
     * Set gender
     *
     * @param string $gender
     * @return self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get gender
     *
     * @return string $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Add answer
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer $answer
     */
    public function addAnswer(\Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer $answer)
    {
        $this->answers[] = $answer;
    }

    /**
     * Remove answer
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer $answer
     */
    public function removeAnswer(\Ibtikar\GlanceDashboardBundle\Document\QuestionAnswer $answer)
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
}
