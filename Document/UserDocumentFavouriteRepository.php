<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * UserDocumentFavouriteRepository
 *
 * This class was generated by the Doctrine ODM. Add your own custom
 * repository methods below.
 */
class UserDocumentFavouriteRepository extends DocumentRepository
{

    public function getUserDocumentFavourite($documentId, $userId)
    {
        return $this->findOneBy(array('document.$id' => new \MongoId($documentId), 'user.$id' => new \MongoId($userId)));
    }

    public function getDocumentReadLater($documentId)
    {
        return $this->findBy(array('document.$id' => new \MongoId($documentId)));
    }

    public function getUserDocumentFavouriteList($userId, $limit = 10, $skip = 0, $user = null)
    {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:UserDocumentFavourite')
                ->field('user.$id')->equals(new \MongoId($userId));
//!! THIS CODITION WAS REMOVED AS THE STAR FLAG IS IN THE USER DOCUMENT NOT IN UserDocumentFavourite
//        if (!(is_object($user) && $user->getStar())) {
//            $queryBuilder->field('goodyStar')->equals(FALSE);
//        }
        return $queryBuilder->eagerCursor(true)->skip($skip)->limit($limit)->sort(array('createdAt' => 'DESC'))->getQuery()->execute();
    }
}
