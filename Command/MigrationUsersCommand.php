<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;
use Ibtikar\GlanceUMSBundle\Document\Visitor;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MigrationUsersCommand extends ContainerAwareCommand {

    private $dataDir;
    private $dm;
    private $time;

    protected function configure() {
        $this->dataDir = __DIR__ . "/../DataFixtures/WPData/";

        $this
                ->setName('migration:user:start')
                ->setDescription('Goody Kitchen data migration from json object.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->time = new \DateTime();
        $total = 5873;
        $data = array();
        $helper = $this->getHelper('question');
        $question = new Question('How many records you want to migrate?[' . $total . ']: ', $total);
        $question2 = new Question('Do you want to start from spacific offset?[1]: ', 1);
        $answer = $helper->ask($input, $output, $question);
        $offset = $helper->ask($input, $output, $question2);
        $output->writeln("");
        if ($answer > 0 && $answer <= $total) {
            $progress = new ProgressBar($output, $answer);

            // start and displays the progress bar
            $progress->start();
            $progress->setOverwrite(true);

            $i = 0;

            $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

            $user = $this->dm->getRepository('IbtikarGlanceUMSBundle:Staff')->findOneByUsername('goodyAdmin');

            $filePath = __DIR__ . "/../DataFixtures/WPData/contacts-filtered.csv";

            $readFromFile = fopen($filePath, 'r');

            while (!feof($readFromFile) && $i < $answer + $offset - 1) {
                $i++;
                $contactInfo = fgetcsv($readFromFile);
                $email = $contactInfo[3];

                $existVisitor = $this->dm->getRepository('IbtikarGlanceUMSBundle:Visitor')->findBy(array('email'=>  strtolower($email)));
                if (count($existVisitor) == 0) {

                    $visitor = new Visitor();

                    $visitor->setNickName(trim(str_replace('_', '-', $contactInfo[2]), "-"));
                    $visitor->setUsername(trim(str_replace('_', '-', $contactInfo[2]), "-"));
                    $visitor->setEmail($contactInfo[3]);
                    $oldPass = $this->generatePassword(10);
                    $visitor->setUserPassword($oldPass);
                    $visitor->setMustChangePassword(true);

                    $visitor->setMigrated(true);

                    $this->dm->persist($visitor);
                    $this->dm->flush();
                    $this->sendMail($visitor, $oldPass);
                    $progress->advance(1);

//            $this->getContainer()->get('Signup')->sendWelcomeMail($visitor, $this->getContainer()->get('router')->generate('login',array(),TRUE), true, true);
                }
//            try{
//        $errorsObjects = $this->getContainer()->get('validator')->validate($visitor,null,array('visitorSignup'));
//            } catch (\Exception $e){
//            die(var_dump($e->getTraceAsString()));
//            }
//        if (count($errorsObjects) > 0) {
//            foreach ($errorsObjects as $error) {
//                die(var_dump($visitor->getUsername(),$error->getPropertyPath(),$error->getMessage()));
//            }
//        }
            }
            fclose($readFromFile);

//            $this->dm->flush();

            $output->writeln(PHP_EOL . $answer . " Users were added.");
        } else {
            $output->writeln(array("      (Wrong Answer)", "(ノಠ益ಠ)ノ"));
        }
    }

    private function sendMail($visitor, $oldPass)
    {
        $emailTemplate = $this->dm->getRepository('IbtikarGlanceDashboardBundle:EmailTemplate')->findOneByName('add frontent user');

        $body = str_replace(
            array(
            '%user-name%',
            '%email%',
            '%password%',
            '%loginUrlAr%',
            '%loginUrlEn%',
            ), array(
            $visitor->getNickName(),
            $visitor->getEmail(),
            $oldPass,
            $this->getContainer()->get('router')->generate('ibtikar_goody_frontend_login', array('_locale' => 'ar'), UrlGeneratorInterface::ABSOLUTE_URL),
            $this->getContainer()->get('router')->generate('ibtikar_goody_frontend_login', array('_locale' => 'en'), UrlGeneratorInterface::ABSOLUTE_URL),
            ), str_replace('%message%', $emailTemplate->getTemplate(), $this->getContainer()->get('frontend_base_email')->getBaseRender2($visitor->getPersonTitle(), false))
        );
        $mailer = $this->getContainer()->get('swiftmailer.mailer.spool_mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($emailTemplate->getSubject())
            ->setFrom($this->getContainer()->getParameter('mailer_user'))
            ->setTo($visitor->getEmail())
            ->setBody($body, 'text/html')
        ;
        $mailer->send($message);
    }

    private function generatePassword($length = 8)
    {

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@!_-';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }
}
