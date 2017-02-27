<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecipeAlignCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('recipe:align')
            ->setDescription('this command adjust alignment for migrated recipes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array(
            'migrated' => true
        ));

        foreach ($recipes as $recipe) {
            $recipe->setIngredients(str_replace("'direction:rtl", "'text-align: right;direction: rtl; display: block; padding-right: 30px;", $recipe->getIngredients()));
//                die(var_dump(strpos($recipe->getIngredientsEn(), 'span')));
            if(strpos($recipe->getIngredientsEn(), 'span') === false){
                $recipe->setIngredientsEn("<span style='text-align: left; display: block; padding-left: 30px;'>".$recipe->getIngredientsEn()."</span>");
            }
            $output->writeln($recipe->getSlug()." <=> ".$recipe->getSlugEn());
        }
        $dm->flush();
        $output->writeln("Command finished sucessfully");
    }
}
