<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AutoPublishRecipeCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('autopublish:recipe')
            ->setDescription('Auto publish recipe.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array(
            'status' => \Ibtikar\GlanceDashboardBundle\Document\Recipe::$statuses['autopublish'],
            'autoPublishDate' => array('$lt' => new \DateTime())
        ));
        $successCount = 0;
        $failCount = 0;

        foreach ($recipes as $recipe) {
            $locations = array();
            foreach ($recipe->getPublishLocations() as $publishlocation) {
                $location = $dm->getRepository('IbtikarGlanceDashboardBundle:Location')->findOneBy(array('section' => $publishlocation->getSection(), 'page' => $publishlocation->getPage()));
                if ($location && $location->getIsSelectable()) {
                    $locations [] = $location;
                }
            }

            $publishResult = $this->getContainer()->get('recipe_operations')->publishAutopublish($recipe, $locations);
            if ($publishResult['status'] === 'error') {
                $failCount++;
            } else {
                $successCount++;

//            if ($comic->getPublishedBy() && $comic->getPublishedBy()->getEnabled() && $comic->getPublishedBy()->getEmailtoComicPublisher()) {
                $output->write("sending email");
                $emailTemplate = $dm->getRepository('IbtikarGlanceDashboardBundle:EmailTemplate')->findOneByName('auto publish recipe');
//            $status = $this->getContainer()->get('translator')->trans('AutoPublish recipe', array(), 'recipe');
                $body = str_replace(
                    array(
                    '%fullname%',
                    '%link%',
                    '%shortTitle%',
                    '%title%',
                    '%message%',
                    '%status%',
                    '%time%',
                    '%date%',
                    '%smallMessage%',
                    '%extraInfo%',
                    '%color%',
                    '%type%',
                    ), array(
                    $recipe->getPublishedBy()->__toString(),
                    $this->getContainer()->get('router')->generate('ibtikar_goody_frontend_'.trim($recipe->getType()).'_view', array('slug' => $recipe->getSlug(), '_locale' => 'ar'), UrlGeneratorInterface::ABSOLUTE_URL),
                    $recipe->getTitle(),
                    $recipe->getTitle(),
                    $emailTemplate->getMessage(),
                    'تم النشر تلقائياً'
                    ,
                    $recipe->getPublishedAt()->format('h:i a'),
                    $recipe->getPublishedAt()->format('d/m/Y'),
                    $emailTemplate->getSmallMessage(), str_replace(array('%date%', '%time%'), array($recipe->getPublishedAt()->format('d/m/Y'), $recipe->getPublishedAt()->format('h:i a')), $emailTemplate->getExtraInfo()),
                    $this->getContainer()->getParameter('themeColor'),
                    $this->getContainer()->get('translator')->trans($recipe->getType(), array(), 'recipe')
                    ), str_replace('%extra_content%', $emailTemplate->getTemplate(), $this->getContainer()->get('base_email')->getBaseRender($recipe->getPublishedBy()->getPersonTitle()))
                );
                $subject = str_replace('%shortTitle%', mb_substr($recipe->getTitle(), 0, 10, 'utf-8'), $emailTemplate->getSubject());
                $receiver = $recipe->getPublishedBy()->getEmail();
                $this->sendMail($body, $subject, $receiver);
//            }
            }
        }
        $output->write("Command finished sucessfully");
        if ($successCount > 0) {
            $output->write(" <info>published $successCount recipes</info>");
        }
        if ($failCount > 0) {
            $output->write(" <error>failed in publish $failCount recipes</error>");
        }
        $output->writeln("");
    }

    function sendMail($body, $subject, $receiver)
    {
        $mailer = $this->getContainer()->get('swiftmailer.mailer.spool_mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->getContainer()->getParameter('mailer_user'))
            ->setTo($receiver)
            ->setBody($body, 'text/html');
        $mailer->send($message);
    }
}
