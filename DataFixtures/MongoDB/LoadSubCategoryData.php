<?php

namespace Ibtikar\GlanceDashboardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ibtikar\GlanceUMSBundle\Document\Staff;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Ibtikar\GlanceDashboardBundle\Document\Category;



class LoadSubCategoryData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface {

    public function load(ObjectManager $manager) {
      $subCategory= new Category();
      $subCategory->setName('مطبخ الطوارئ');
      $subCategory->setNameEn('Kitchen 911');
      $subCategory->setSlug('مطبخ-الطوارئ');
      $subCategory->setSlugEn('Kitchen-911');
      $subCategory->setNotModified(TRUE);
      $subCategory->setParent($this->getReference("المدونة"));
      $manager->persist($subCategory);

      $subCategory1= new Category();
      $subCategory1->setName('نصائح');
      $subCategory1->setNameEn('Tips');
      $subCategory1->setSlug('نصائح');
      $subCategory1->setSlugEn('Tips');
      $subCategory1->setNotModified(TRUE);
      $subCategory1->setParent($this->getReference("المدونة"));
      $manager->persist($subCategory1);

      $subCategory2= new Category();
      $subCategory2->setName('مقالات');
      $subCategory2->setNameEn('Articles');
      $subCategory2->setSlug('مقالات');
      $subCategory2->setSlugEn('Articles');
      $subCategory2->setNotModified(TRUE);
      $subCategory2->setParent($this->getReference("المدونة"));
      $manager->persist($subCategory2);



        $manager->flush();
    }

    public function getOrder() {
        return 2;
    }

}
