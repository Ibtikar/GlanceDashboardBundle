<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class MoveRedirectsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('redirect:move')
                ->setDescription('move added redirects to seo redirect');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        // get the records
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $redirects = $dm->getRepository('IbtikarGlanceDashboardBundle:SeoRedirect')->findBy(array('deleted'=>false));

        $redirect = $this->getContainer()->get('redirect');

        $output->writeln("Pushing redirects to redirect table");

        foreach ($redirects as $seoRedirect) {
            $output->writeln($seoRedirect->getOldUrl());
            $output->writeln($seoRedirect->getRedirectToUrl());

            $redirect->addPermanentRedirect($seoRedirect->getOldUrl(), $seoRedirect->getRedirectToUrl());

            $output->writeln("Delete entry from seo redirect");
            $seoRedirect->delete($dm);
        }

        $dm->flush();


        $output->writeln("Moved to redirect table");

    }
}
