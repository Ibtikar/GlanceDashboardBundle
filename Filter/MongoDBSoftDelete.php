<?php

namespace Ibtikar\GlanceDashboardBundle\Filter;

use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;
use Doctrine\ODM\MongoDB\Mapping\ClassMetaData;


class MongoDBSoftDelete extends BsonFilter {

    /**
     * @param ClassMetaData $class
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $class) {
        if (is_a($class->getReflectionClass()->getName(), 'Ibtikar\GlanceUMSBundle\Document\User', true)) {
            return array();
        }
        if (is_subclass_of($class->getReflectionClass()->getName(), 'Ibtikar\GlanceDashboardBundle\Document\Document')) {
            return array('deleted' => false);
        }
    }

}
