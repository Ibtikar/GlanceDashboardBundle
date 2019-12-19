<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Ibtikar\GlanceDashboardBundle\Document\Course;

/**
 * CourseRepository
 *
 */
class CourseRepository extends DocumentRepository {

    public function getRecentPublishCourse() {
        return $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Course')
                        ->field('deleted')->equals(FALSE)
                        ->field('status')->equals(Course::$statuses['published'])
                        ->sort('publishedAt', 'DESC')
                        ->limit(1)
                        ->getQuery()->getSingleResult();
    }


    public function getCourses($skip = 0, $limit = 30)
    {
        $q = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Course')
            ->field('deleted')->equals(FALSE)
            ->field('status')->in(array(
                Course::$statuses['published'],
                Course::$statuses['unpublished']
            ))
            ->sort('publishedAt', 'DESC');

        return $q->limit($limit)
                ->skip($skip)
                ->getQuery()
                ->execute();
    }

    public function getCoursescount()
    {
        $q = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Course')
            ->field('deleted')->equals(FALSE)
            ->field('status')->in(array(
                Course::$statuses['published'],
                Course::$statuses['unpublished']
            ))
            ->sort('publishedAt', 'DESC');

        return $q->getQuery()
                ->count();
    }
}
