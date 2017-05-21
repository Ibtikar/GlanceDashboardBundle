<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

class FixBugRediectCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('redirect:deleted:recipe')
            ->setDescription('this command is used to redirect recipe that publish before to this list instead of 404.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array(
            'status' => array('$in' => array(Recipe::$statuses['autopublish'], Recipe::$statuses['deleted'], Recipe::$statuses['draft'])),
            'publishedAt' => array('$exists' => true)
        ));

        $output->write("count recipes ".count($recipes));


        foreach ($recipes as $recipe) {
            if ($recipe->getSlug()) {
                var_dump($recipe->getId());
                var_dump($recipe->getSlug());
                $this->getContainer()->get('redirect')->removeRedirect($this->getContainer()->get('recipe_operations')->getFrontEndUrl($recipe));
                $this->getContainer()->get('redirect')->removeRedirect($this->getContainer()->get('recipe_operations')->getFrontEndUrlEn($recipe));
                $this->getContainer()->get('recipe_operations')->hideFrontEndUrl($recipe);
            } else {
                var_dump($recipe->getId());
            }
        }
        $dm->flush();
        $output->write("Command finished sucessfully");

    }


}
