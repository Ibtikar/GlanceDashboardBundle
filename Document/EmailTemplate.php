<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\EmailTemplateRepository")
 * @MongoDB\Index(keys={"name"="asc"})
 */
class EmailTemplate extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\String
     */
    private $template;

    /**
     * @MongoDB\String
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $subject;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $message;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $emailDataRecord;

    /**
     * @MongoDB\String
     */
    private $smallMessage = '';

    /**
     * @MongoDB\String
     */
    private $extraInfo;

    /**
     * @MongoDB\String
     */
    private $locale;

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
     * Set template
     *
     * @param string $template
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get template
     *
     * @return string $template
     */
    public function getTemplate()
    {
        return $this->template;
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
     * Set subject
     *
     * @param string $subject
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get subject
     *
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }
    /**
     * Set locale
     *
     * @param string $message
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
     * Set emailDataRecord
     *
     * @param string $emailDataRecord
     * @return self
     */
    public function setEmailDataRecord($emailDataRecord)
    {
        $this->emailDataRecord = $emailDataRecord;
        return $this;
    }

    /**
     * Get emailDataRecord
     *
     * @return string $emailDataRecord
     */
    public function getEmailDataRecord()
    {
        return $this->emailDataRecord;
    }

    /**
     * Set smallMessage
     *
     * @param string $smallMessage
     * @return self
     */
    public function setSmallMessage($smallMessage)
    {
        $this->smallMessage = $smallMessage;
        return $this;
    }

    /**
     * Get smallMessage
     *
     * @return string $smallMessage
     */
    public function getSmallMessage()
    {
        return $this->smallMessage;
    }

    /**
     * Set extraInfo
     *
     * @param string $extraInfo
     * @return self
     */
    public function setExtraInfo($extraInfo)
    {
        $this->extraInfo = $extraInfo;
        return $this;
    }

    /**
     * Get extraInfo
     *
     * @return string $extraInfo
     */
    public function getExtraInfo()
    {
        return $this->extraInfo;
    }
}
