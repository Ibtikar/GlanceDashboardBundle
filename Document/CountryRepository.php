<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author Moemen Hussein <momen.shaaban@ibtikar.net.sa>
 * CountryRepository
 */
class CountryRepository extends DocumentRepository {


    public function findCountrySorted() {

        return $this->getDocumentManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:Country')
                ->sort('specialCountrySort', 'DESC')
                ->sort('countryUsageCount', 'DESC')
                ->sort('countryName', 'ASC');
    }
}
