<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class fixTagIssueeCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('fix:local:tag')
            ->setDescription('fix tag local')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findAll();


        foreach ($recipes as $recipe) {
            $tagsAr = $recipe->getTags();
            if (count($tagsAr) > 0) {
                foreach ($tagsAr as $tagAr) {
                    if (!$tagAr->getTag()) {
                        $output->writeln(" <info>tag insert in arabic " . $tagAr->getName() . " </info>");
                        $tagAr->setTag($tagAr->getName());
                    }
                }
            }

            $tagsEn = $recipe->getTagsEn();
            if (count($tagsEn) > 0) {
                foreach ($tagsEn as $tagEn) {
                    if (!$tagEn->getTagEn()) {
                        $output->writeln(" <info>tag insert in english " . $tagEn->getName() . " </info>");
                        $tagEn->setTagEn($tagEn->getName());
                    }
                }
            }
        }
        $dm->flush();
        $output->write("Command finished sucessfully");

        $output->writeln("");
    }
}
