<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Ibtikar\GlanceDashboardBundle\Document\FacebookUpdateDocument;

/**
 * @author Ola <ola.ali@ibtikar.net.sa>
 */
class FaceBookDocumentUpdateCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('updatedocument:facebook')
                ->setDescription('update facebook data for document')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $url = 'https://graph.facebook.com';
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $id = array();
        $documents = $dm->getRepository('IbtikarGlanceDashboardBundle:FacebookUpdateDocument')->findBy(array('status' => FacebookUpdateDocument::$statuses['new']));
        if (count($documents) > 0) {
            foreach ($documents as $document) {
                $id[] = $document->getId();
            }
            $dm->createQueryBuilder('IbtikarGlanceDashboardBundle:FacebookUpdateDocument')
                    ->update()
                    ->field('id')->in($id)
                    ->field('status')->set(FacebookUpdateDocument::$statuses['inprogress'])
                    ->multiple(true)
                    ->getQuery()->execute();

            foreach ($documents as $document) {
                $data = array('id' => $this->getContainer()->get('router')->generate('ibtikar_goody_frontend_'.$document->getType().'_view', array('slug' => $document->getDocument()->getSlug(), '_locale'=> 'ar'), true), 'scrape' => TRUE);
                $ch = curl_init("https://graph.facebook.com");

                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $data
                ));

                curl_exec($ch);
                curl_close($ch);

                $document->setStatus(FacebookUpdateDocument::$statuses['done']);
            }

            $dm->flush();
            if (!gc_enabled()) {
                gc_enable();
            }
            $dm->clear();
            gc_collect_cycles();
        }
        $output->writeln("Command finished sucessfully");
    }

}
