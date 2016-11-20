<?php

namespace Ibtikar\GlanceDashboardBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Ibtikar\GlanceDashboardBundle\Validator\Constraints as CustomAssert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @MongoDB\EmbeddedDocument
 */
class Phone {

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $phone;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    private $countryCode;

    public function getPhone() {
        return $this->phone;
    }

    public function getCountryCode() {
        return $this->countryCode;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
        return $this;
    }

    public function setCountryCode($countryCode) {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function __toString() {
        return $this->phone;
    }

}
