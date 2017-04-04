<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class SitemapGeneratorCommand extends ContainerAwareCommand {

    private $dm;
    private $saveLocation;

    private $router;
    private $links;

    private $xmlOutputFile;
    private $sitemapXml;
    private $locale="en";
    private $output;

    protected function configure() {
        $this
                ->setName('generate:sitemap')
                ->setDescription('Generate sitemap')
                ->addArgument('locale', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Sitemap Locale')
        ;
    }

    protected function init(){
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $this->saveLocation = __DIR__.'/../../../../web/sitemap-'.$this->locale.'.xml';

        $this->router = $this->getContainer()->get('router');

        $this->links = array();
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->output = $output;
        $locale = $input->getArgument('locale');
        if($locale){
            $this->locale = $locale;
        }
        $this->init();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Beginning sitemap generation');
            $output->writeln('Generating list of URLs...');
        }

        $this->generateLinks();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) $output->writeln('Generating XML file...');

        $this->writeXML($this->links);

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) $output->writeln('Complete!');
    }

    /*******************************************************/


    protected function generateLinks(){
        $this->addLinkNormal('ibtikar_goody_frontend_homepage');
        $this->addLinkNormal('ibtikar_goody_frontend_daily_timeLine_');
        $this->addLinkNormal('ibtikar_goody_frontend_recipes_');
        $this->addLinkNormal('ibtikar_goody_frontend_products_');
        $this->addLinkNormal('ibtikar_goody_frontend_magazines_');
        $this->addLinkNormal('ibtikar_goody_frontend_articles_');
        $this->addLinkNormal('ibtikar_goody_frontend_tips_');
        $this->addLinkNormal('ibtikar_goody_frontend_stars_');
        $this->addLinkNormal('ibtikar_goody_frontend_kitchen911_');
        $this->addLinkNormal('ibtikar_goody_competition_list_');
        $this->addLinkNormal('ibtikar_goody_competition_list_');


      $this->getLinks();
    }

    protected function getLinks(){
        $posts = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('status'=>'publish','deleted'=>false));
        foreach($posts as $post){
            $this->addLink($post);
        }
    }

    protected function addLink($material, $lastmod = null, $changefreq = "daily", $priority = "0.8") {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE && ($material->getSlugEn() == "" || $material->getSlug() == "")) {
            $this->output->writeln("bad content (".$material->getTitle().") slug : ".$material->getSlug().", en slug : ".$material->getSlugEn());
            return;
        }
        $arr = array(
                'loc' => $this->generateURL('ibtikar_goody_frontend_view',array('slug' => $this->locale == "en"?$material->getSlugEn():$material->getSlug(),'_locale'=>$this->locale)),
                'changefreq' => "monthly", //always, hourly, daily, weekly, monthly, yearly, never
                'priority' => "0.8",
                'title' =>$material->getTitle(),
                );


        if($material->getUpdatedAt()){
            $arr['lastmod'] = $material->getUpdatedAt()->format('Y-m-d');
        }
        $this->links[] = $arr;
    }


    protected function addLinkNormal($routePrefix) {
        $arr = array(

                'loc' => $this->generateURL($routePrefix.(substr($routePrefix, -1) == "_"?$this->locale:""),array('_locale' => $this->locale)),
                'changefreq' => "monthly", //always, hourly, daily, weekly, monthly, yearly, never
                'priority' => "0.8"
            );

//        if($material->getUpdatedAt()){
//            $arr['lastmod'] = $material->getUpdatedAt()->format('Y-m-d');
//        }
        $this->links[] = $arr;
    }
    /*******************************************************/

    protected function writeXML($list){
      $this->initAppXmlObj();
      $this->updateXmlFile($list);
    }

    /**
     * Appends a row to a specific xml object
     *
     * @param SimpleXMLElement $xmlElement xml object for appending data
     * @param array $array row to add to the xml element
     * @return SimpleXMLElement
     */
    protected function addRow(\SimpleXMLElement $xmlElement, array $array){
        $record = $xmlElement->addChild('url');
        $record->addChild('loc', str_replace('m.', '',htmlspecialchars($array['loc'])));
        $record->addChild('changefreq', $array['changefreq']);
        $record->addChild('priority', $array['priority']);

        $link = $record->addChild('xhtml:xhtml:link',null);

        $link->addAttribute("rel","alternate");
        $link->addAttribute("href",htmlspecialchars($array['loc']));

        return $xmlElement;
    }

   /**
     * Updates the Xml object laying in SAVE_LOCATION
     *
     * @param $rows array an array of sitemap links
     */
    protected function updateXmlFile(array $rows){
        foreach ($rows as $rowArray) {
            $this->addRow($this->sitemapXml, $rowArray);
        }
        $dom = dom_import_simplexml($this->sitemapXml)->ownerDocument;
        $dom->formatOutput = true;
        file_put_contents($this->xmlOutputFile, $dom->saveXML());
    }

   /**
     * Initiates the appXml object
     * @param InputInterface $input
     */
    private function initAppXmlObj(){
        $this->sitemapXml = new \SimpleXMLElement('<urlset/>');
        $this->sitemapXml->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->sitemapXml->addAttribute('xmlns:xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-news/0.9 http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd');
        $this->sitemapXml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->sitemapXml->addAttribute('xmlns:xmlns:news', 'http://www.google.com/schemas/sitemap-news/0.9');
        $this->sitemapXml->addAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        $this->sitemapXml->addAttribute('encoding', 'UTF-8');
        $this->xmlOutputFile = $this->saveLocation;
    }

    private function generateURL($route, $params=array()){
      return $this->router->generate($route, $params,\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
    }
}