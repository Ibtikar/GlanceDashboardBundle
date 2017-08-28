<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;

class SubProductSlugCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('slug:subproduct')
            ->setDescription('slug:subproduct')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $subproducts = $dm->getRepository('IbtikarGlanceDashboardBundle:SubProduct')->findBy(array(
            'type' => 'subproduct',
        ));
        $successCount = 0;
        $failCount = 0;

        foreach ($subproducts as $subproduct) {
            if(!$subproduct->getSlug()){
        $slugAr = ArabicMongoRegex::slugify($subproduct->getName()."-".  $subproduct->getCreatedAt()->format('ymdHis'));
        $slugEn = ArabicMongoRegex::slugify($subproduct->getNameEn()."-".$subproduct->getCreatedAt()->format('ymdHis'));
        $subproduct->setSlug($slugAr);
        $subproduct->setSlugEn($slugEn);

        $slug = new Slug();
        $slug->setReferenceId($subproduct->getId());
        $slug->setType(Slug::$TYPE_SUBPRODUCT);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);

            }
        }
          $dm->flush();


    }


}
