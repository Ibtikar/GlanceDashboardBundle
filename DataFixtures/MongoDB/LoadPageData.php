<?php

namespace Ibtikar\GlanceDashboardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Intl\Intl;
use Ibtikar\GlanceDashboardBundle\Document\Page;

class LoadPageData implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $page = new Page();
        $page->setTitle('magazine page');
        $page->setBrief('مرجع متكامل مليء بالوصفات والأفكار والإلهام المتجدد!
واحة غنية بالأفكار والوصفات والحلول المبتكرة والممتعة التي تثري حياتك وحياة عائلتك، تجدينها في كل عدد من أعداد مجلة مطبخ قودي.
انضمي إلى أكثر من نصف مليون سيدة قرأت المجلة واستفادت من محتواها بنسختها الورقية، وعبر تطبيق مجلة مطبخ قودي على الآندرويد ومتجر التطبيقات.');
        $page->setBriefEn('A complete reference of recipes, ideas and inspirations!

Our Magazines are rich with ideas, recipes and entertaining innovative solutions that enrich your life and the lives of your family, you will find it in each issue of the Goody kitchen Magazine.
Join more than half a million females that have read the magazine and benefited from the content of the printed copies and through the application of Goody Kitchen Magazine on App store and Google play.');
        $page->setUrl('https://land.ly/goody');
        $page->setNotModified(TRUE);
        $manager->persist($page);
        $manager->flush();
    }
}
