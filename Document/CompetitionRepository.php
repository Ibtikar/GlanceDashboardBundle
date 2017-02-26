<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Ibtikar\GlanceDashboardBundle\Document\Competition;

/**
 * CompetitionRepository
 *
 */
class CompetitionRepository extends DocumentRepository {

    public function getRecentPublishCompetition() {
        return $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
                        ->field('deleted')->equals(FALSE)
                        ->field('status')->equals(Competition::$statuses['published'])
                        ->sort('publishedAt', 'DESC')
                        ->limit(1)
                        ->getQuery()->getSingleResult();
    }


    public function getCompetitions($skip = 0, $limit = 30)
    {
        $q = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
            ->field('deleted')->equals(FALSE)
            ->field('status')->in(array(
                Competition::$statuses['published'],
                Competition::$statuses['unpublished']
            ))
            ->sort('publishedAt', 'DESC');

        return $q->limit($limit)
                ->skip($skip)
                ->getQuery()
                ->execute();
    }

    public function getCompetitionscount()
    {
        $q = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Competition')
            ->field('deleted')->equals(FALSE)
            ->field('status')->in(array(
                Competition::$statuses['published'],
                Competition::$statuses['unpublished']
            ))
            ->sort('publishedAt', 'DESC');

        return $q->getQuery()
                ->count();
    }
}
