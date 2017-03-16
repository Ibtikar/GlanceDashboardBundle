<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceDashboardBundle\Document\UserDocumentLike;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Ibtikar\GlanceUMSBundle\Document\User;


class UserLike {

    /** @var DocumentManager $dm */
    private $dm;

    private $container;


    /**
     * @param ManagerRegistry $mr
     */
    public function __construct(ManagerRegistry $mr, $container) {
        $this->dm = $mr->getManager();
        $this->container = $container;

    }


    public function like(Document $document, User $user) {
        $UserDocumentLike = $this->dm->getRepository('IbtikarGlanceDashboardBundle:UserDocumentLike')->getUserDocumentLike($document->getId(), $user->getId());
        if ($UserDocumentLike) {
            return false;
        }
        $documentLike = new UserDocumentLike();
        $documentLike->setUser($user);
        $documentLike->setDocument($document);
        if(method_exists($document, 'getType')){
            $documentLike->setType($document->getType());
        }else{
            $documentLike->setType(get_class($document), strrpos(get_class($document), '\\') + 1);
        }
        $this->dm->persist($documentLike);
        $document->setNoOfLikes($document->getNoOfLikes() + 1);
        $this->dm->flush();
        return true;
    }


    public function unlike(Document $document, User $user) {
        $userDocumentLike = $this->dm->getRepository('IbtikarGlanceDashboardBundle:UserDocumentLike')->getUserDocumentLike($document->getId(), $user->getId());
        if ($userDocumentLike) {
            $userDocumentLike->delete($this->dm, $user);
            $document->setNoOfLikes($document->getNoOfLikes() - 1);
            $this->dm->flush();
            return true;
        }
        return false;
    }

}
