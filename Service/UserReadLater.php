<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceDashboardBundle\Document\UserDocumentReadLater;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceUMSBundle\Document\User;

/**
 * @author Moemen Hussein <moemen.hussein@ibtikar.net.sa>
 */
class UserReadLater {

    /** @var DocumentManager $dm */
    private $dm;

    /**
     * @param ManagerRegistry $mr
     */
    public function __construct(ManagerRegistry $mr) {
        $this->dm = $mr->getManager();
    }

    /**
     * @author Moemen Hussein <moemen.hussein@ibtikar.net.sa>
     * @param Document $document
     * @param User $user
     * @return boolean
     */
    public function read(Document $document, User $user) {
        $userDocumentReadLater = $this->dm->getRepository('IbtikarGlanceDashboardBundle:UserDocumentReadLater')->getUserDocumentReadLater($document->getId(), $user->getId());
        if ($userDocumentReadLater) {
            return false;
        }
        $documentReadLater = new UserDocumentReadLater();
        $documentReadLater->setUser($user);
        $documentReadLater->setDocument($document);
        $documentReadLater->setDocumentType($document->getType());
        $this->dm->persist($documentReadLater);
        $this->dm->flush();
        return true;
    }

    /**
     * @author Moemen Hussein <moemen.hussein@ibtikar.net.sa>
     * @param Document $document
     * @param User $user
     * @return boolean
     */
    public function unread(Document $document, User $user) {
        $userDocumentReadLater = $this->dm->getRepository('IbtikarGlanceDashboardBundle:UserDocumentReadLater')->getUserDocumentReadLater($document->getId(), $user->getId());
        if ($userDocumentReadLater) {
            $userDocumentReadLater->delete($this->dm, $user);
            $this->dm->flush();
            return true;
        }
        return false;
    }

}
