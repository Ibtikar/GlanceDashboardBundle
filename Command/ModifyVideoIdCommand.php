<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ModifyVideoIdCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('modify:video:id')
            ->setDescription('this command used to correct image that did not appear due to wrong vid')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $mediacount = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Media')
                ->field('vid')->exists(true)
                ->getQuery()->count();
        $output->writeln("media with video" . $mediacount);

        for ($i = 0; $i < $mediacount; $i = $i + 500) {
            $medias = $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Media')
                    ->field('vid')->exists(true)
                    ->eagerCursor(true)
                    ->skip($i)->limit(500)
                    ->getQuery()->execute();

            foreach ($medias as $media) {
                $newVideo = str_replace('[:]', '', str_replace('[:en]', '', $media->getVid()));
                if (strpos($newVideo, '?')) {
                    $vedioArray = explode('?', $newVideo);
                    $media->setVid($vedioArray[0]);
                } else {
                    $media->setVid($newVideo);
                }
            }

            $dm->flush();
            gc_collect_cycles();
            $dm->clear();
        }
        $output->writeln("Command finished sucessfully");
    }
}
