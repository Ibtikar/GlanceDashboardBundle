<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class chiefCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('chief:back')
            ->setDescription('this command used to return chiefs deleted')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $staff = $dm->getRepository('IbtikarGlanceUMSBundle:Staff')->findBy(array(
            'type' => 'staff',
            'deleted' => TRUE
        ));
        $output->writeln("user deleted".count($staff));

        foreach ($staff as $user) {
            $output->writeln("user deleted id".$user->getId());
            $user->setDeleted(FALSE);
        }
        $dm->flush();
        $output->writeln("Command finished sucessfully");
    }
}
