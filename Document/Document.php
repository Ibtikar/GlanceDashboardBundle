<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceUMSBundle\Document\User;

/**
 * @MongoDB\InheritanceType("COLLECTION_PER_CLASS")
 * @MongoDB\MappedSuperclass
 * @MongoDB\Index(keys={"deleted"="desc"})
 */
class Document {

    /**
     * @MongoDB\Date
     */
    private $createdAt;

    /**
     * @MongoDB\Date
     */
    public $updatedAt;

    /**
     * @MongoDB\Date
     */
    protected $deletedAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\User")
     */
    private $createdBy;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\User")
     */
    public $updatedBy;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\User")
     */
    protected $deletedBy;

    /**
     * @MongoDB\Boolean
     */
    private $deleted = false;

     /**
     * @MongoDB\Boolean
     */
    private $notModified=false;


    public function delete(DocumentManager $dm, User $user = null) {
        if ($user) {
            $this->deletedBy = $user;
        }
        $this->deletedAt = new \DateTime();
        $this->deleted = true;
        $this->removeDocumentReferences($dm, $user);
        $this->updateReferencesCounts(-1);
    }

    /**
     * @author Maisara Khedr <maisara@ibtikar.net.sa>
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param null|\Ibtikar\GlanceUMSBundle\Document\User $user
     */
    protected function removeDocumentReferences(DocumentManager $dm, User $user = null) {
        $documentClassFullPath = get_called_class();
        $documentNamespaceParts = explode('\\', $documentClassFullPath);
        $documentClass = array_pop($documentNamespaceParts);
        // we will not delete the users references as we need it in every collection for deleted by and updated by references
        if ($documentClass === 'Staff' || $documentClass === 'Visitor') {
            return;
        }
        $documentFullPathReference = "targetDocument=\"$documentClassFullPath\"";
        $documentRelativePathReference = "targetDocument=\"$documentClass\"";
        $ignoredDocumentsClasses = array(
            'Ibtikar\GlanceUMSBundle\Document\Document',
            'Ibtikar\GlanceUMSBundle\Document\User',
            'Ibtikar\VisitorBundle\Document\Social\Facebook',
            'Ibtikar\VisitorBundle\Document\Social\Google',
            'Ibtikar\VisitorBundle\Document\Social\LinkedIn',
            'Ibtikar\VisitorBundle\Document\Social\Twitter',
            'Ibtikar\VisitorBundle\Document\Social\Yahoo',
            'Ibtikar\BackendBundle\Document\Phone',
            'Ibtikar\GlanceDashboardBundle\Document\SubProduct'
        );
        $collectionsClasses = array();
        $mappedDocumentsClasses = $dm->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        foreach ($mappedDocumentsClasses as $mappedDocumentClass) {
            if (!in_array($mappedDocumentClass, $ignoredDocumentsClasses)) {
                $namespaceParts = explode('\\', $mappedDocumentClass);
                $bundleShortName = $namespaceParts[0] . $namespaceParts [1] . ':' . $namespaceParts[3];
                $collectionsClasses[$bundleShortName] = $mappedDocumentClass;
            }
        }
        foreach ($collectionsClasses as $pakageclass => $targetDocument) {
            $docClass = new \ReflectionClass($targetDocument);
            $props = $docClass->getProperties();
            foreach ($props as $prop) {
                $annotationsText = $prop->getDocComment();
                $annotations = array();
                preg_match_all('#@(.*?)\n#s', $annotationsText, $annotations);
                $referenceFound = false;
                $queryBuilder = $dm->createQueryBuilder($pakageclass)
                                ->update()
                                ->multiple(true)
                                ->field($prop->name)->equals($this->getId());
                foreach ($annotations[1] as $annotation) {
                    // check if the variable is marked to be ignored from the delete
                    if (strpos($annotation, 'KeepReference') !== false) {
                        $referenceFound = false;
                        break;
                    }
                    if (strpos($annotation, $documentFullPathReference) !== false || strpos($annotation, $documentRelativePathReference) !== false) {
                        if (strpos($annotation, 'ReferenceOne') !== false) {
                            $queryBuilder->field($prop->name)->set(null);
                            $referenceFound = true;
                            continue;
                        }
                        if (strpos($annotation, 'ReferenceMany') !== false) {
                            $queryBuilder->field($prop->name)->pull($this->getId());
                            $referenceFound = true;
                        }
                    }
                }
                if (!$referenceFound) {
                    continue;
                }
//                if ($user) {
//                    $queryBuilder->field('updatedBy')->set($user);
//                }
                $queryBuilder
                        ->field('updatedAt')->set(new \DateTime())
                        ->getQuery()
                        ->execute();
            }
        }
    }

    /**
     * increase or decrease the count of this object in the referenced documents
     * the referenced class should set the count type to <b>Increment</b> not Int
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param integer $value
     */
    public function updateReferencesCounts($value) {
        if(filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \Exception("A non integer value passed to updateReferencesCounts function. The passed value is '$value'");
        }
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Set createdBy
     *
     * @param Ibtikar\GlanceUMSBundle\Document\User $createdBy
     * @return self
     */
    public function setCreatedBy(\Ibtikar\GlanceUMSBundle\Document\User $createdBy) {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return Ibtikar\GlanceUMSBundle\Document\User $createdBy
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param Ibtikar\GlanceUMSBundle\Document\User $updatedBy
     * @return self
     */
    public function setUpdatedBy(\Ibtikar\GlanceUMSBundle\Document\User $updatedBy) {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return Ibtikar\GlanceUMSBundle\Document\User $updatedBy
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Set deletedBy
     *
     * @param Ibtikar\GlanceUMSBundle\Document\User $deletedBy
     * @return self
     */
    public function setDeletedBy(\Ibtikar\GlanceUMSBundle\Document\User $deletedBy) {
        $this->deletedBy = $deletedBy;
        return $this;
    }

    /**
     * Get deletedBy
     *
     * @return Ibtikar\GlanceUMSBundle\Document\User $deletedBy
     */
    public function getDeletedBy() {
        return $this->deletedBy;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return self
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean $deleted
     */
    public function getDeleted() {
        return $this->deleted;
    }


    /**
     * Set notModified
     *
     * @param boolean $notModified
     * @return self
     */
    public function setNotModified($notModified)
    {
        $this->notModified = $notModified;
        return $this;
    }

    /**
     * Get notModified
     *
     * @return boolean $notModified
     */
    public function getNotModified()
    {
        return $this->notModified;
    }

}
