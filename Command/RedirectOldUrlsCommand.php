<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ibtikar\GlanceDashboardBundle\Document\SeoRedirect;

class RedirectOldUrlsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('redirect:old:urls')
                ->setDescription('redirect old urls')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $filePath = __DIR__ . '/404batchurl.csv';
        if (!is_file($filePath)) {
            $output->writeln("<error>The file $filePath was not found</error>");
            return;
        }
        $totalRecords = 0;
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $readFromFile = fopen($filePath, 'r');
        while (!feof($readFromFile)) {
            $totalRecords++;
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $output->writeln("<comment>Checking the record number $totalRecords</comment>");
            }
            $redirectInfo = fgetcsv($readFromFile);

            $oldUrl = $redirectInfo[0];
            $redirectUrl = $redirectInfo[1];
            if ($oldUrl && $redirectUrl) {
                $oldUrl = str_replace('https://www.goodykitchen.com', '', $oldUrl);
                $oldUrl = str_replace('http://m.goodykitchen.com', '', $oldUrl);
                $redirectUrl = str_replace('https://www.goodykitchen.com', '', $redirectUrl);

                if (strpos($oldUrl, '?') !== FALSE) {
                    $oldUrlArray = explode('?', $oldUrl);
                    $oldUrl = $oldUrlArray[0];
                }
                $seoRedirct = new SeoRedirect();
                $seoRedirct->setOldUrl($oldUrl);
                $seoRedirct->setRedirectToUrl($redirectUrl);
                $output->writeln("<info>$oldUrl => $redirectUrl</info>");
                $dm->persist($seoRedirct);
            }
        }
        $dm->flush();
        fclose($readFromFile);
        $output->writeln("<info>Command found  $totalRecords records</info>");
    }

}
