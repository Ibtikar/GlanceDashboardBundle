<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @author Maisara Khedr
 */
class SystemSettings {

    protected $dm;

    public function __construct(ManagerRegistry $mr,$container) {
        $this->dm = $mr->getManager();
        $this->container = $container;
    }

    public function getSettingsRecord($key) {
        $settingsRecord = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Settings')->findOneBy(array("key" => $key));
        return $settingsRecord;
    }

    public function getSettingsValue($key) {
        return $this->getSettingsRecord($key)->getValue();
    }

    public function getSettingsRecords($keysArray) {
        $settingsRecords = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Settings')
                ->field('key')->in($keysArray)
                ->getQuery()->execute();
        return $settingsRecords;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param array $settingsRecords
     * @return array
     */
    private function convertRecordsToArray($settingsRecords) {
        $settings = array();
        foreach ($settingsRecords as $record) {
            if($record->getKey()=='stars-brief-ar'){
                $settings['metaTagTitleAr']=$record->getMetaTagTitleAr();
                $settings['metaTagDesciptionAr']=$record->getMetaTagDesciptionAr();
                $settings['metaTagTitleEn']=$record->getMetaTagTitleEn();
                $settings['metaTagDesciptionEn']=$record->getMetaTagDesciptionEn();
            }
            $settings[$record->getKey()] = $record->getValue();
        }
        return $settings;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param array $keysArray
     * @return array
     */
    public function getSettingsAsArray(array $keysArray) {
        return $this->convertRecordsToArray($this->getSettingsRecords($keysArray));
    }

    public function getSettingsRecordsByCategory($category) {
        $settingsRecords = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Settings')
                ->field('categories.'.$category)->exists(true)
                ->getQuery()->execute();
        return $settingsRecords;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param string $category
     * @return array
     */
    public function getSettingsByCategoryAsArray($category,$withPayload=false) {
        return $withPayload?$this->convertRecordsToArrayWithPayload($this->getSettingsRecordsByCategory($category)):$this->convertRecordsToArray($this->getSettingsRecordsByCategory($category));
    }

}
