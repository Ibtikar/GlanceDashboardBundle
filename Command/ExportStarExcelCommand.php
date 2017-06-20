<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ibtikar\GlanceDashboardBundle\Document\Export;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;



class ExportStarExcelCommand extends ContainerAwareCommand implements SingleRunCommandInterface  {

    private $container;
    private $dm;
    private $limit;
    private $currentDate;
    private $stopCommand = false;
    private $commandLogPrefix;
    protected $defaultParams = array('sort' => 'createdAt',
        'columnDir' => 'asc');

    public function __construct($name = null) {
        parent::__construct($name);
        $this->limit = 1000;
        $date = new \DateTime();
        $this->currentDate = "[" . $date->format('d-m-Y') . "]";
    }

    public function stopCommand() {
        $this->stopCommand = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandLogPrefix() {
        return $this->commandLogPrefix;
    }

    protected function configure() {
        $this
                ->setName('export:star:excel')
                ->setDescription('export excel from export collection and remove the 100 days old files')
//            ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->stopCommand) {
            $output->writeln('<error>The command is already running in another process exiting now.</error>');
            return;
        }

        $this->container = $this->getContainer();

        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->output = $output;

        $exportQuery = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Export');
        $output->writeln("Removing 100 days old files.");
        $this->removeOldFiles();

        $result = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Export')
                        ->field('state')->equals(Export::READY)
                        ->field('type')->equals(Export::STARS)
                        ->getQuery()->execute();

        $output->writeln("Start generating excel files.");

        $this->generateSheets($result);

        $output->writeln("Generating excel files finished.");
        $output->writeln("The files will be removed after creation with 100 days.");
    }

    private function removeOldFiles() {

        $files = glob($this->container->getParameter('xls_temp_path') . "*");
        $count = 0;
        foreach ($files as $file) {
            if (time() - filemtime($file) >= 8640000) {
                unlink($file);
                $count++;
            }
        }

        $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Export')
                ->remove()
                ->multiple(true)
                ->field('state')->equals(Export::FINISHED)
//                ->field('createdAt')->lt(new \DateTime('2 days ago'))
                ->getQuery()->execute();

        $this->output->writeln("Removed " . $count . " files");
    }

//function printMemoryUsage()
//    {
//        $this->output->writeln(sprintf('Memory usage (currently) %dKB/ (max) %dKB', round(memory_get_usage(true) / 1024), memory_get_peak_usage(true) / 1024));
//    }


    private function getStarList($export, $queryCount=false, $skip=0, $limit=1000) {
        $queryBuilder = $this->dm->createQueryBuilder('IbtikarGlanceDashboardBundle:Stars')->field('deleted')->equals(false);

        $params = array_merge($this->defaultParams, $export->getParams());

        if (isset($params['ids'])) {
            $queryBuilder = $queryBuilder->field('id')->in($params['ids']);
        }
        if (isset($params['status'])) {
            $queryBuilder = $queryBuilder->field('status')->equals($params['status']);
        }
        if($queryCount) {
            return $queryBuilder->getQuery()->execute()->count();
        }

        $queryBuilder = $queryBuilder->sort($params['sort'], $params['columnDir'])
                                     ->limit($limit)
                                     ->skip($skip)
                                     ->eagerCursor(true);

        return $queryBuilder;
    }

    private function generateSheets($result) {
//        $this->printMemoryUsage();
        foreach ($result as $export) {
            $exportId = $export->getId();
            $exportName = $export->getName();
            $this->nextState($export);
            $createExcel = $this->container->get('create_excel');
            $count = $this->getStarList($export, true);
            $skip = 0;
            $index = 1;

            while ($skip < $count) {
                $queryBuilder = $this->getStarList($export, false, $skip);
                $result = $queryBuilder->getQuery()->execute();

//                if (is_null($export->getFields())) {
                $createExcel->setFields(array_reverse(array('name', 'email','mobile','createdAt','city','birthDate','married','children','employee','qualities','howTo','meaning','famousDish')));
//              
//                  } else {
//                    $createExcel->setFields(array_reverse($export->getFields()));
//                }
                $createExcel->setQuery($queryBuilder->getQuery());
                $createExcel->setCollection($result);
                $createExcel->setExtension('.'.$export->getExtension());
                $fileName = $index.'-'.$export->getName() . $this->currentDate . '.' .'xls';
                $createExcel->saveFile($fileName);

                $index++;
                $skip += count($result);
                $this->dm->clear();
            }

            $export = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Export')->find($exportId);

            $this->createZip($export->getName(), 'xls');
//            $this->printMemoryUsage();
            $this->nextState($export);
            $this->sendMail($export->getCreatedBy(), $export->getName());
        }
    }

    private function nextState($export) {
        $state = $export->getState();

        if ($state == Export::READY) {
            $next = Export::IN_PROGRESS;
        } elseif ($state == Export::IN_PROGRESS) {
            $next = Export::FINISHED;
        }

        if (isset($next)) {
            $export->setState($next);
            $this->dm->persist($export);
            $this->dm->flush();
        }
        return $export->getState();
    }

    private function createZip($prefix, $extension='xls') {
        $tempPath = $this->container->getParameter('xls_temp_path');
        $files = glob($tempPath . "*" . $prefix . "*");
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }
        sort($valid_files, SORT_NATURAL);

        if (count($valid_files)) {
            //create the archive
            $zip = new \ZipArchive();
            if (!$zip->open($tempPath . $prefix . $this->currentDate . ".zip", \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE)) {
                return false;
            }
            //add the files
            foreach ($valid_files as $index => $file) {
                if (!file_exists($file)) {
                    continue;
                }

                $zip->addFile($file, ($index + 1) . $this->currentDate . ".".$extension);
            }
            $zip->close();

            foreach ($valid_files as $file) {
                unlink($file);
            }

            //check to make sure the file exists
            return file_exists($tempPath);
        } else {
            return false;
        }
    }

    private function sendMail($user, $filename) {
        $emailTemplate = $this->dm->getRepository('IbtikarGlanceDashboardBundle:EmailTemplate')->findOneByName('export visitors excel');
        $filesavename = $this->container->get('translator')->trans('excel exported file', array(), 'stars');
        $body = str_replace(
                array(
            '%fullname%',
            '%smallMessage%',
            '%extraInfo%',
            '%color%',
            '%message%',
            '%export_url%',
            '%file_name%'
            ), array(
            $user->__toString(),
            $emailTemplate->getSmallMessage(),
            $emailTemplate->getExtraInfo(),
            $this->container->getParameter('themeColor'),
            'بناءً على طلبكم وحسب البيانات المطلوبه من النظام, تم الإنتهاء من تصدير ملف نجمات مطبخ قودى
نرجو الضغط على الرابط لتحويلك الى الملف
 ',
            $this->generateUrl('ibtikar_glance_ums_export_download', array('id'=>$user->getId(),'filename' => $filename, 'filesavename' => $this->container->get('translator')->trans('excel exported file', array(), 'stars')),UrlGeneratorInterface::ABSOLUTE_URL),
            $filesavename
            )
            , str_replace('%extra_content%', $emailTemplate->getTemplate(), $this->container->get('base_email')->getBaseRender($user->getPersonTitle())));
        $mailer = $this->container->get('swiftmailer.mailer.spool_mailer');
        $message = \Swift_Message::newInstance()
                ->setSubject('تصدير ملف نجمات مطبخ قودى ')
                ->setFrom($this->container->getParameter('mailer_user'))
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html')
        ;
        $mailer->send($message);
    }

    private function generateUrl($route, $parameters = array(), $referenceType = true) {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

}
