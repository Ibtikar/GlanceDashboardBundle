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
class UserDocumentLike extends Document
{

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\ReferenceOne(discriminatorField="type", discriminatorMap={"recipe"="Ibtikar\GlanceDashboardBundle\Document\Recipe","competition"="Ibtikar\GlanceDashboardBundle\Document\Competition"})
     */
    private $document;

    /**
     * @MongoDB\String
     */
    private $documentType;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\User", discriminatorField="type", discriminatorMap={"visitor"="Ibtikar\GlanceUMSBundle\Document\Visitor", "staff"="Ibtikar\GlanceUMSBundle\Document\Staff"})
     */
    private $user;

    /**
     * @MongoDB\String

     */
    private $type;


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
     * Set document
     *
     * @param $document
     * @return self
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return $document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return self
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
        return $this;
    }

    /**
     * Get documentType
     *
     * @return string $documentType
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set user
     *
     * @param Ibtikar\GlanceUMSBundle\Document\User $user
     * @return self
     */
    public function setUser(\Ibtikar\GlanceUMSBundle\Document\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return Ibtikar\GlanceUMSBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }
}
