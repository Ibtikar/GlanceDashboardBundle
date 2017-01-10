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
        $location->setType(array('recipe','article','tip','kitchen911'));
        $location->setMaxNumberOfMaterials(1);
        $location->setRequiredCoverImage(true);
        $manager->persist($location);

        $location = new Location();
        $location->setPage('Home');
        $location->setSection('home-magazine');
        $location->setIsSelectable(TRUE);
        $location->setType(array('magazine'));
        $location->setMaxNumberOfMaterials(4);
        $location->setRequiredCoverImage(true);
        $manager->persist($location);

        $manager->flush();
    }

}
