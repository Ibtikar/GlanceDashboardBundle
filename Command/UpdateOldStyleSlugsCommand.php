<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdateOldStyleSlugsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('url:fix')
                ->setDescription('Remove the random numbers from slugs');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        // get the records
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $posts = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('status'=>'publish','deleted'=>false));

        $router = $this->getContainer()->get('router');
        $redirect = $this->getContainer()->get('redirect');
        $changed = 0;
        $unchanged = 0;
        $output->writeln("starting with recipe");

        foreach ($posts as $post) {

//            if($changed == 1){
//                break;
//            }

            // check of slug contain number
            $newSlugAr = preg_replace('/\-\d+/', "", $post->getSlug());
            $newSlugEn = preg_replace('/\-\d+/', "", $post->getSlugEn());

            $oldSlugAr = $post->getSlug();
            $oldSlugEn = $post->getSlugEn();

            $arabicCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('deleted')->equals(FALSE)
                    ->field('slug')->equals($newSlugAr)
                    ->field('id')->notEqual($post->getId())->
                    getQuery()->execute()->count();

            $englishCount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
                    ->field('deleted')->equals(FALSE)
                    ->field('slugEn')->equals($newSlugEn)
                    ->field('id')->notEqual($post->getId())->
                    getQuery()->execute()->count();

            if ($newSlugAr != $post->getSlug()) { // assume one check is enough
                $changed++;
                $slug = $dm->getRepository('IbtikarGlanceDashboardBundle:Slug')->findOneBy(array('referenceId' => $post->getId()));

                if ($slug) {
                    if ($arabicCount == 0) {
                        $slug->setSlugAr($newSlugAr);
                    }
                    if ($englishCount == 0) {
                        $slug->setSlugEn($newSlugEn);
                    }
                }
                if ($arabicCount == 0) {

                    $post->setSlug($newSlugAr);
                }
                if ($englishCount == 0) {

                    $post->setSlugEn($newSlugEn);
                }
            } else {
                $unchanged++;
            }
            if ($post->getSlug()) {
                if ($arabicCount != 0) {
                    $newSlugAr = $oldSlugAr;
                }
                if ($englishCount != 0) {
                    $newSlugEn = $oldSlugEn;
                }
                $output->writeln("Change arabic slug '$oldSlugAr' to '$newSlugAr'");
                $output->writeln("Change english slug '$oldSlugEn' to '$newSlugEn'");
                $redirect->addPermanentRedirect($router->generate('ibtikar_goody_frontend_view', array('slug' => $oldSlugAr, '_locale' => 'ar')), $router->generate('ibtikar_goody_frontend_' . trim($post->getType()) . '_view', array('slug' => $newSlugAr, '_locale' => 'ar')));
                $redirect->addPermanentRedirect($router->generate('ibtikar_goody_frontend_view', array('slug' => $oldSlugEn, '_locale' => 'en')), $router->generate('ibtikar_goody_frontend_' . trim($post->getType()) . '_view', array('slug' => $newSlugEn, '_locale' => 'en')));
            }
        }

        $dm->flush();
        //product

        $posts = $dm->getRepository('IbtikarGlanceDashboardBundle:Product')->findBy(array('deleted'=>false));


        $changed = 0;
        $unchanged = 0;
        $output->writeln("Changed '$changed', Already fixed '$unchanged'");

        $output->writeln("starting with product");


        foreach ($posts as $post) {

//            if($changed == 1){
//                break;
//            }

            // check of slug contain number
            $newSlugAr = preg_replace('/\-\d+/', "", $post->getSlug());
            $newSlugEn = preg_replace('/\-\d+/', "", $post->getSlugEn());

            if($newSlugAr != $post->getSlug()){ // assume one check is enough
                $changed++;

                $oldSlugAr = $post->getSlug();
                $oldSlugEn = $post->getSlugEn();

                $output->writeln("Change arabic slug '$oldSlugAr' to '$newSlugAr'");
                $output->writeln("Change english slug '$oldSlugEn' to '$newSlugEn'");

                $slug = $dm->getRepository('IbtikarGlanceDashboardBundle:Slug')->findOneBy(array('referenceId' => $post->getId()));

                if($slug){
                    $slug->setSlugAr($newSlugAr);
                    $slug->setSlugEn($newSlugEn);
                }

                $post->setSlug($newSlugAr);
                $post->setSlugEn($newSlugEn);

                $redirect->addPermanentRedirect($router->generate('ibtikar_goody_frontend_view', array('slug' => $oldSlugAr, '_locale' => 'ar')), $router->generate('ibtikar_goody_frontend_view', array('slug' => $newSlugAr, '_locale' => 'ar')));
                $redirect->addPermanentRedirect($router->generate('ibtikar_goody_frontend_view', array('slug' => $oldSlugEn, '_locale' => 'en')), $router->generate('ibtikar_goody_frontend_view', array('slug' => $newSlugEn, '_locale' => 'en')));
            } else {
                $unchanged++;
            }
        }
        $dm->flush();


        $output->writeln("Changed '$changed', Already fixed '$unchanged'");

    }
}
