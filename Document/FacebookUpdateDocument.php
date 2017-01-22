<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @author Gehad Mohamed
 */

/**
 * @MongoDB\Document
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"status"="asc"}),
 * })
 */
class FacebookUpdateDocument {

    public static $statuses = array(
        "new" => "new",
        "inprogress" => "inprogress",
        "done" => "done",
    );

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(discriminatorField="type", discriminatorMap={"recipe"="Ibtikar\GlanceDashboardBundle\Document\Material"})
     */
    private $document;

    /**
     * @MongoDB\String
     */
    private $status = 'new';

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set document
     *
     * @param $document
     * @return self
     */
    public function setDocument($document) {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return $document
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus() {
        return $this->status;
    }

}
