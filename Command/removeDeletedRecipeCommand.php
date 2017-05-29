<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class removeDeletedRecipeCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('deleted:recipe')
            ->setDescription('delete recipe')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $recipes = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                ->field('status')->equals(Recipe::$statuses['deleted'])
                ->field('deleted')->equals(false)
                ->field('title')->equals(new \MongoRegex(('/' . "مكرر" . '/i')))
                ->getQuery()->execute();
        $output->writeln("recipes count " . count($recipes));
        foreach ($recipes as $recipe) {
            $output->writeln("recipe Id " . $recipe->getId());
            $output->writeln("recipe title " . $recipe->getTitle());
            $recipe->setDeleted(TRUE);
        }
        $dm->flush();

        $output->writeln("command finished successfully");
    }
}
