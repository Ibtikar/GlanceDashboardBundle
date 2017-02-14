<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Intl\Intl;

class LoadCountryEnDataCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('load:countryEn')
            ->setDescription('load country en')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        \Locale::setDefault('en');
        $countries = Intl::getRegionBundle()->getCountryNames();
        foreach ($countries as $countryCode => $name) {
            if (in_array($countryCode, array('AC', 'BL', 'BQ', 'CW', 'DG', 'EA', 'GG', 'IC', 'SS', 'SX', 'TA'))) {
                continue;
            }
            if ($countryCode !== 'XK') {
                $country = $dm->getRepository('IbtikarGlanceUMSBundle:Country')->findOneByCountryCode($countryCode);
                if ($country) {
                    $country->setCountryNameEn($name);
                }
            }
        }
        $dm->flush();
        $output->write("Command finished sucessfully");
    }
}
