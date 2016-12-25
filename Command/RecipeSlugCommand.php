<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ibtikar\GlanceDashboardBundle\Service\ArabicMongoRegex;
use Ibtikar\GlanceDashboardBundle\Document\Slug;

class RecipeSlugCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('slug:recipe')
            ->setDescription('slug:recipe')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array(
//            'status' => \Ibtikar\GlanceDashboardBundle\Document\Recipe::$statuses['publish'],
        ));
        $successCount = 0;
        $failCount = 0;

        foreach ($recipes as $recipe) {
            if(!$recipe->getSlug()){
        $slugAr = ArabicMongoRegex::slugify($recipe->getTitle()."-".  $recipe->getCreatedAt()->format('ymdHis'));
        $slugEn = ArabicMongoRegex::slugify($recipe->getTitleEn()."-".$recipe->getCreatedAt()->format('ymdHis'));
        $recipe->setSlug($slugAr);
        $recipe->setSlugEn($slugEn);

        $slug = new Slug();
        $slug->setReferenceId($recipe->getId());
        $slug->setType(Slug::$TYPE_RECIPE);
        $slug->setSlugAr($slugAr);
        $slug->setSlugEn($slugEn);
        $dm->persist($slug);

            }
        }
          $dm->flush();


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
