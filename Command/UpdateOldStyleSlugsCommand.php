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

        foreach ($posts as $post) {

            if($changed == 1){
                break;
            }

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
