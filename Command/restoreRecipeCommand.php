<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

class restoreRecipeCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('restore:content')
            ->setDescription('restore content deleted by mistake')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $mainAdmin = $dm->getRepository('IbtikarGlanceUMSBundle:Staff')->findOneBy(array('username' => 'goodyAdmin'));

        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array(
            'status' => Recipe::$statuses['deleted'],
            'deletedBy.$id' => new \MongoId($mainAdmin->getId()),
            'deletedAt' => array('$gte' => new \DateTime('2017-03-30'))
        ));
        $output->writeln("no of recipes deleted".count($recipes));
        $successCount = 0;

        foreach ($recipes as $recipe) {
            if($recipe->getAutoPublishDate()){
            $output->writeln("recipe id".$recipe->getId().' autopublish date '.$recipe->getAutoPublishDate()->format('m/d/Y H:i A'));
            $recipe->setStatus( Recipe::$statuses['autopublish']);
            }
        }
        $dm->flush();
        $output->writeln("Command finished sucessfully");

    }


}
