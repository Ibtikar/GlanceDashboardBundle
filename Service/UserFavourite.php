<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceDashboardBundle\Document\UserDocumentFavourite;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceUMSBundle\Document\User;


class UserFavourite {

    /** @var DocumentManager $dm */
    private $dm;

    /**
     * @param ManagerRegistry $mr
     */
    public function __construct(ManagerRegistry $mr) {
        $this->dm = $mr->getManager();
    }


    public function read(Document $document, User $user) {
        $UserDocumentFavourite = $this->dm->getRepository('IbtikarGlanceDashboardBundle:UserDocumentFavourite')->getUserDocumentFavourite($document->getId(), $user->getId());
        if ($UserDocumentFavourite) {
            return false;
        }
        $documentReadLater = new UserDocumentFavourite();
        $documentReadLater->setUser($user);
        $documentReadLater->setDocument($document);
        $documentReadLater->setDocumentType($document->getType());
        $this->dm->persist($documentReadLater);
        $this->dm->flush();
        return true;
    }


    public function unread(Document $document, User $user) {
        $UserDocumentFavourite = $this->dm->getRepository('IbtikarGlanceDashboardBundle:UserDocumentFavourite')->getUserDocumentFavourite($document->getId(), $user->getId());
        if ($UserDocumentFavourite) {
            $UserDocumentFavourite->delete($this->dm, $user);
            $this->dm->flush();
            return true;
        }
        return false;
    }

}
