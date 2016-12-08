<?php

namespace Ibtikar\GlanceDashboardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ibtikar\GlanceDashboardBundle\Document\Location;

/**
 * @author Ola <ola.ali@ibtikar.net.sa>
 */
class LoadLocationsData implements FixtureInterface {

    public function load(ObjectManager $manager) {


        $location = new Location();
        $location->setPage('Home');
        $location->setSection('Daily-solution');
        $location->setIsSelectable(TRUE);
        $location->setType(array('recipe'));
        $location->setMaxNumberOfMaterials(1);
        $location->setRequiredCoverImage(true);
        $manager->persist($location);
        $manager->flush();
    }

}