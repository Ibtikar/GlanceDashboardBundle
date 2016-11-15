<?php

namespace Ibtikar\GlanceDashboardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ibtikar\GlanceUMSBundle\Document\Country;
use Symfony\Component\Intl\Intl;
use Ibtikar\GlanceDashboardBundle\Document\Category;

class LoadCategoryData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setName('الوصفات');
        $category->setNameEn('Recipes');
        $category->setSlug('الوصفات');
        $category->setSlugEn('Recipes');
        $category->setParent(null);
        $category->setNotModified(TRUE);
        $manager->persist($category);

        $category1 = new Category();
        $category1->setName('منتجات قودي	');
        $category1->setNameEn('Products');
        $category1->setSlug('منتجات-قودي	');
        $category1->setSlugEn('Products');
        $category1->setParent(null);
        $category1->setNotModified(TRUE);

        $manager->persist($category1);

        $category2 = new Category();
        $category2->setName('المجلة');
        $category2->setNameEn('Magazine');
        $category2->setSlug('المجلة');
        $category2->setSlugEn('Magazine');
        $category2->setParent(null);
        $category2->setNotModified(TRUE);

        $manager->persist($category2);



        $category3 = new Category();
        $category3->setName('الأكاديمية الإلكترونية');
        $category3->setNameEn('Online Academy');
        $category3->setSlug('الأكاديمية-الإلكترونية');
        $category3->setSlugEn('Online-Academy');
        $category3->setNotModified(TRUE);

        $category3->setParent(null);
        $manager->persist($category3);



        $category4 = new Category();
        $category4->setName('المدونة');
        $category4->setNameEn('Blog');
        $category4->setSlug('المدونة');
        $category4->setSlugEn('Blog');
        $category4->setParent(null);
        $manager->persist($category4);

        $category5 = new Category();
        $category5->setName('نجماتنا');
        $category5->setNameEn('Stars');
        $category5->setSlug('نجماتنا');
        $category5->setSlugEn('Stars');
        $category5->setNotModified(TRUE);
        $category5->setParent(null);
        $manager->persist($category5);

        $category6 = new Category();
        $category6->setName('تواصلي معنا');
        $category6->setNameEn('Contact us');
        $category6->setSlug('تواصلي-معنا');
        $category6->setNotModified(TRUE);
        $category6->setSlugEn('Contact-us');
        $category6->setParent(null);
        $manager->persist($category6);



        $manager->flush();
        $this->addReference('المدونة', $category4);
    }

    public function getOrder()
    {
        return 1;
    }
}
