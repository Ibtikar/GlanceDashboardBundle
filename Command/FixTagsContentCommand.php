<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FixTagsContentCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('fix:recipe:tag')
            ->setDescription('make tags in recipe one fields')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findAll();
        $successCount = 0;
        $failCount = 0;

        foreach ($recipes as $recipe) {
            $tagArabicName = array();
            $tagEnglishName = array();
            $tagsName = array();
            $tagsId=array();
            $tagsObject = array();

            $tagsAr = $recipe->getTags();
            $tagsEn = $recipe->getTagsEn();
            if (count($tagsAr) > 0) {
                foreach ($tagsAr as $tag) {
                    $tagArabicName[] = $tag->getTag();
                }
                $output->writeln("recipe of id  " . $recipe->getId() . ' has arabic tags ' . implode(',', $tagArabicName));

                $newTagsAr = $dm->getRepository('IbtikarGlanceDashboardBundle:RecipeTag')->findBy(array(
                    'name' => array('$in' => $tagArabicName)));

                foreach ($newTagsAr as $newTagAr) {
                    if (!in_array($newTagAr->getId(), $tagsId)) {
                        $tagsId[] = $newTagAr->getId();
                        $tagsName[] = $newTagAr->getName();

                        $tagsObject[] = $newTagAr;
                    }
                }
            }
            if (count($tagsEn) > 0) {
                foreach ($tagsEn as $tag) {
                    $tagEnglishName[] = $tag->getTagEn();
                }
                $output->writeln("recipe of id  " . $recipe->getId() . ' has english tags ' . implode(',', $tagEnglishName));


                $newTagsEn = $dm->getRepository('IbtikarGlanceDashboardBundle:RecipeTag')->findBy(array(
                    'nameEn' => array('$in' => $tagEnglishName)));

                foreach ($newTagsEn as $newTagEn) {
                    if (!in_array($newTagEn->getId(), $tagsId)) {
                        $tagsId[] = $newTagEn->getId();
                        $tagsName[] = $newTagEn->getName();
                        $tagsObject[] = $newTagEn;
                    }
                }
            }
            foreach ($tagsObject as $tagObject) {
                $recipe->addRecipeTag($tagObject);
            }
            $dm->flush();
            $output->writeln("recipe of id  " . $recipe->getId() . ' will have tags ' . implode(',', $tagsName));
            $output->writeln("==========================================================");
        }

    }
}
