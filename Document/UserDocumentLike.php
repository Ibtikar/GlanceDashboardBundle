<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\UserDocumentLikeRepository")
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"document.$id"="asc", "user.$id"="asc"}),
 * })
 */
class UserDocumentLike extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(discriminatorField="type", discriminatorMap={"material"="Ibtikar\AppBundle\Document\Material", "comics"="Ibtikar\AppBundle\Document\Comics","comment"="Ibtikar\AppBundle\Document\Comment","questionnaire"="Ibtikar\AppBundle\Document\Questionnaire"})
     */
    private $document;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\User", discriminatorField="type", discriminatorMap={"visitor"="Ibtikar\VisitorBundle\Document\Visitor", "staff"="Ibtikar\BackendBundle\Document\Staff"})
     */
    private $user;

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
     * @param Ibtikar\GlanceDashboardBundle\Document\Document $document
     * @return self
     */
    public function setDocument(\Ibtikar\GlanceDashboardBundle\Document\Document $document) {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Document $document
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * Set user
     *
     * @param Ibtikar\GlanceUMSBundle\Document\User $user
     * @return self
     */
    public function setUser(\Ibtikar\GlanceUMSBundle\Document\User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return Ibtikar\GlanceUMSBundle\Document\User $user
     */
    public function getUser() {
        return $this->user;
    }

}
