<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class HistoryRepository extends DocumentRepository
{

    /**
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     * @param string $materialId
     * @param string $sortOrder 'ASC' or 'DESC' default is 'ASC'
     * @return array
     */

    public function getMaterialHistory($materialId, $sortOrder = 'ASC',$limit = null) {
        return $this->dm->createQueryBuilder('IbtikarAppBundle:History')
                        ->field('deleted')->equals(FALSE)
                        ->field('material')->equals($materialId)
                        ->sort('createdAt', $sortOrder)
                        ->limit($limit)
                        ->getQuery()->execute();
    }

    public function getLastRecord($materialId){
        $lastMaterial = $this->getMaterialHistory($materialId, 'DESC', 1);

        if($lastMaterial->hasNext()){
            return $lastMaterial->getNext();
        } else {
            return null;
        }

    }

}
