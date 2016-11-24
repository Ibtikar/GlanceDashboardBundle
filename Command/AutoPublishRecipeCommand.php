<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;


class AutoPublishRecipeCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('autopublish:recipe')
                ->setDescription('Auto publish recipe.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array(
            'status' => \Ibtikar\GlanceDashboardBundle\Document\Recipe::$statuses['autopublish'],
            'autoPublishDate' => array('$lt' => new \DateTime())
        ));
        $successCount = 0;
        $failCount = 0;
        foreach ($recipes as $recipe) {
            $locations = array();
            foreach ($recipe->getPublishLocations() as $publishlocation) {
                $location = $dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findOneBy(array('section' => $publishlocation->getSection(), 'page' => $publishlocation->getPage()));
                if ($location && $location->getIsSelectable()) {
                    $locations [] = $location;
                }
            }
            $publishResult = $this->getContainer()->get('recipe_operations')->publish($recipe, $locations);
            if ($publishResult['status'] === 'error') {
                $failCount++;
            } else {
                $successCount++;
            }
        }
        $output->write("Command finished sucessfully");
        if ($successCount > 0) {
            $output->write(" <info>published $successCount recipes</info>");
        }
        if ($failCount > 0) {
            $output->write(" <error>failed in publish $failCount recipes</error>");
        }
        $output->writeln("");
    }

}
