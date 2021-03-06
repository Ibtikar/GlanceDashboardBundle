<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * RecipeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecipeRepository extends DocumentRepository
{

    public function getDailySolution()
    {
        return $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['publish'])
                ->field('publishLocations.section')->equals('Daily-solution')
                ->sort('publishedAt', 'DESC')
                ->getQuery()->getSingleResult();
    }

    public function getPreviousDailySolution($skip = 0, $limit = 4, $user = null)
    {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['publish'])
                ->field('publishLocations.section')->notEqual('Daily-solution')
                ->field('dailysolutionDate')->exists(TRUE)
                ->field('deleted')->equals(FALSE)
                ->field('coverPhoto')->prime(true);
        if (!(is_object($user) && $user->getStar())) {
            $queryBuilder->field('goodyStar')->equals(FALSE);
        }
        return $queryBuilder->sort('publishedAt', 'DESC')
                ->eagerCursor()
                ->limit($limit)
                ->skip($skip)
                ->getQuery()->execute();
    }

    public function getMostView($skip = 0, $limit = 4, $user = null)
    {
        $date = new \DateTime();
        $date->modify("-1 month");
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['publish'])
                ->field('deleted')->equals(FALSE);
        if (!(is_object($user) && $user->getStar())) {
            $queryBuilder->field('goodyStar')->equals(FALSE);
        }

        return $queryBuilder->field('coverPhoto')->prime(true)
                ->field('publishedAt')->gte($date)
                ->sort('noOfViews', 'DESC')
                ->eagerCursor()
                ->limit($limit)
                ->skip($skip)
                ->getQuery()->execute();
    }

    /**
     * @author Ahmad Gamal <a.gamal@ibtikar.net.sa>
     * @param string $tagId
     * @param integer $limit
     * @param integer $skip
     * @return object
     */
    public function getContentInTag($tagId, $limit, $skip,$user=null) {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                        ->field('status')->equals(Recipe::$statuses['publish'])
                        ->field('deleted')->equals(FALSE)
                        ->field('coverPhoto')->prime(true);

        $queryBuilder->addOr($queryBuilder->expr()->field('recipeTags')->in($tagId));
        if (!(is_object($user) && $user->getStar())) {
            $queryBuilder->field('goodyStar')->equals(FALSE);
        }

        $queryBuilder->sort('publishedAt', 'DESC')
                            ->eagerCursor(true)
                            ->limit($limit+1)
                            ->skip($skip)
                            ;

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @author Ahmad Gamal <a.gamal@ibtikar.net.sa>
     * @param string $tagId
     * @return integer
     */
    public function getCountContentInTag($tagId,$user=null) {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                        ->field('status')->equals(Recipe::$statuses['publish'])
                        ->field('deleted')->equals(FALSE);

        $queryBuilder->addOr($queryBuilder->expr()->field('recipeTags')->in($tagId));
//        $queryBuilder->addOr($queryBuilder->expr()->field('tagsEn')->in($tagId));
          if (!(is_object($user) && $user->getStar())) {
            $queryBuilder->field('goodyStar')->equals(FALSE);
        }

        return $queryBuilder->getQuery()->count();
    }

    /**
     * @author Ahmad Gamal <a.gamal@ibtikar.net.sa>
     * @param string $keyword
     * @param integer $limit
     * @param integer $skip
     * @param integer $sort
     * @return object
     */
    public function getSearchContentByKeyword($keyword, $limit, $skip, $sort,$user=null) {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                        ->field('status')->equals(Recipe::$statuses['publish'])
                        ->field('deleted')->equals(FALSE)
                        ->field('coverPhoto')->prime(true);

        $queryBuilder->addOr($queryBuilder->expr()->field('title')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        $queryBuilder->addOr($queryBuilder->expr()->field('titleEn')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));

        $queryBuilder->addOr($queryBuilder->expr()->field('brief')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        $queryBuilder->addOr($queryBuilder->expr()->field('briefEn')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));

        $queryBuilder->addOr($queryBuilder->expr()->field('text')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        $queryBuilder->addOr($queryBuilder->expr()->field('textEn')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));

        if (!(is_object($user) && $user->getStar())) {
            $queryBuilder->field('goodyStar')->equals(FALSE);
        }
        $queryBuilder->sort($sort, 'DESC')
                            ->eagerCursor(true)
                            ->limit($limit+1)
                            ->skip($skip)
                            ;

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @author Ahmad Gamal <a.gamal@ibtikar.net.sa>
     * @param string $keyword
     * @return integer count
     */
    public function getCountSearchContentByKeyword($keyword,$user=null) {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                        ->field('status')->equals(Recipe::$statuses['publish'])
                        ->field('deleted')->equals(FALSE);

        $queryBuilder->addOr($queryBuilder->expr()->field('title')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        $queryBuilder->addOr($queryBuilder->expr()->field('titleEn')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));

        $queryBuilder->addOr($queryBuilder->expr()->field('brief')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        $queryBuilder->addOr($queryBuilder->expr()->field('briefEn')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));

        $queryBuilder->addOr($queryBuilder->expr()->field('text')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        $queryBuilder->addOr($queryBuilder->expr()->field('textEn')->equals(new \MongoRegex(('/' . preg_quote(trim($keyword)) . '/i'))));
        if (!(is_object($user) && $user->getStar())) {
            $queryBuilder->field('goodyStar')->equals(FALSE);
        }

        return $queryBuilder->getQuery()->count();
    }

}
