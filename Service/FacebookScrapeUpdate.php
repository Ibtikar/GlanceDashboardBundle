<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Ibtikar\GlanceDashboardBundle\Document\FacebookUpdateDocument;

/**
 * @author Gehad Mohamed
 */
class FacebookScrapeUpdate {

       protected $dm;

    public function __construct($dm) {
        $this->dm = $dm->getManager();
    }


    public function update($document) {
        if (strpos($document->getStatus(), 'publish') === false) {
            return false;
        }

        try {
            $documentToUpdate = new FacebookUpdateDocument();
            $documentToUpdate->setDocument($document);
            $documentToUpdate->setStatus(FacebookUpdateDocument::$statuses['new']);
            $this->dm->persist($documentToUpdate);
            $this->dm->flush();
        } catch (\Exception $e) {
//            die(var_dump($e->getMessage()));
        }
        $this->dm->flush();
    }





}
