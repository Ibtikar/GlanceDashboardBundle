<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class SitemapAdvanceGeneratorCommand extends ContainerAwareCommand {

    private $dm;
    private $saveLocation;

    private $router;
    private $links;

    private $xmlOutputFile;
    private $sitemapXml;
    private $locale="en";
    private $output;
    private $siteMapFolderAbsolutePath = '';
    private $linksBaseUrl;
    private $sitemapFiles = array(
        'page',
        'recipe',
        'meal',
        'course',
        'ingredient',
        'product',
        'article',
        'tip',
        'kitchen911',
        'tag',
    );

    protected function configure() {
        $this
                ->setName('generate:optimized:sitemap')
                ->setDescription('Generate sitemap')
                ->addArgument('locale', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Sitemap Locale')
//                ->addArgument('path', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Sitemap File Path')
        ;
    }

    protected function init($output){
        $this->linksBaseUrl = $this->getContainer()->getParameter('httpProtocol') . '://' . $this->getContainer()->getParameter('site_domain');
        $this->siteMapFolderAbsolutePath = $this->getContainer()->get('kernel')->getRootDir() . '/../web/sitemap/';
        $fs = new Filesystem();
// create sitemap folder if not exist ( not valid condition)
//        if (!@is_dir($this->siteMapFolderAbsolutePath)) {
//            try {
//                $fs->mkdir($this->siteMapFolderAbsolutePath, 0755);
//            } catch (\Exception $e) {
//                $output->writeln('<error>' . $e->getMessage() . '</error>');
//                return;
//            }
//        } else {
//            try {
//                $fs->remove($this->siteMapFolderAbsolutePath);
//                $fs->mkdir($this->siteMapFolderAbsolutePath, 0755);
//            } catch (\Exception $e) {
//                $output->writeln('<error>' . $e->getMessage() . '</error>');
//                return;
//            }
//        }

        $currentDate = new \DateTime();

        $fileHandler = @fopen($this->siteMapFolderAbsolutePath.'sitemap-'.$this->locale.'.xml', 'w');

        $mainSiteMapContent = str_replace(
                array(
                    '{{baseUrl}}',
                    '{{locale}}',
                    '{{updatedDate}}',
                ),
                array(
                    $this->linksBaseUrl,
                    $this->locale,
                    $currentDate->format(\DateTime::ATOM)
                ),
                file_get_contents($this->siteMapFolderAbsolutePath.'sitemap.xml.tpl'));

        file_put_contents($this->siteMapFolderAbsolutePath.'sitemap-'.$this->locale.'.xml', $mainSiteMapContent);

        $this->router = $this->getContainer()->get('router');

        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();


// pages

// recipes

//blog


//magazine

// meal, ingredient

// product





        $this->links = array();
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->output = $output;
        $locale = $input->getArgument('locale');
        if($locale){
            $this->locale = $locale;
        }
        $this->init($output);

        foreach($this->sitemapFiles as $name){
            $this->saveLocation = $this->siteMapFolderAbsolutePath.$name.'-sitemap-'.$this->locale.'.xml';
            $this->links = array();
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln('Beginning sitemap generation');
                $output->writeln('Generating list of URLs...');
            }

            $this->generateLinks($name);

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) $output->writeln('Generating XML file...');

            $this->writeXML($this->links);

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) $output->writeln('Complete!');

        }


//        $this->generateLinks();
//
//        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) $output->writeln('Generating XML file...');
//
//        $this->writeXML($this->links);
//
//        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) $output->writeln('Complete!');
    }

    /*******************************************************/


    protected function generateLinks($name){
        switch ($name) {
            case 'page':
                $this->addLinkNormal('ibtikar_goody_frontend_homepage');
                $this->addLinkNormal('ibtikar_goody_frontend_daily_timeLine_');
                $this->addLinkNormal('ibtikar_goody_frontend_recipes_');
                $this->addLinkNormal('ibtikar_goody_frontend_products_');
                $this->addLinkNormal('ibtikar_goody_frontend_magazines_');
                $this->addLinkNormal('ibtikar_goody_frontend_articles_');
                $this->addLinkNormal('ibtikar_goody_frontend_tips_');
                $this->addLinkNormal('ibtikar_goody_frontend_stars_');
                $this->addLinkNormal('ibtikar_goody_frontend_kitchen911_');
                break;
            case 'recipe':
                $this->getLinks();
                break;
            case 'meal':
                foreach(Recipe::$mealMap as $meal){
                    $this->addLinkNormal('ibtikar_goody_frontend_category',array(
                        'filter' => 'meal',
                        'value' => str_replace(' ','-',strtolower($meal))
                        ));
                }
                break;
            case 'course':
                foreach(Recipe::$courseMap as $course){
                    $this->addLinkNormal('ibtikar_goody_frontend_category',array(
                        'filter' => 'courses',
                        'value' => str_replace(' ','-',strtolower($course))
                        ));
                }
                break;
            case 'ingredient':
                foreach(Recipe::$keyIngredientMap as $ingredient){
                    $this->addLinkNormal('ibtikar_goody_frontend_category',array(
                        'filter' => 'ingredients',
                        'value' => str_replace(' ','-',strtolower($ingredient))
                        ));
                }
                break;
            case 'product':
                $products = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Product')->findBy(array('deleted' => false));

                foreach($products as $post){
                    $this->addLink($post,'product');
                }
                break;
            case 'article':
                $this->getLinks('article');
                break;
            case 'tip':
                $this->getLinks('tip');
                break;
            case 'kitchen911':
                $this->getLinks('kitchen911');
                break;
            case 'tag':
                $this->getTagLinks('kitchen911');
                break;
        }

//      $this->getLinks();
    }

    protected function getLinks($type = 'recipe'){
        $posts = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('type' => $type,'status'=>'publish','deleted'=>false));
        foreach($posts as $post){
            $this->addLink($post,$type);
        }
    }

    protected function getTagLinks(){
        $tags = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Tag')->findBy(array('deleted'=>false,'tag'.($this->locale == "en"?"En":"")=>array('$exists' => true)));
        foreach($tags as $tag){
            $this->addLinkNormal('ibtikar_goody_frontend_search_tag',array('tag' => $tag->getSlug()));
        }
    }

    protected function addLink($material,$type , $lastmod = null) {

        $nameMethod = (method_exists($material, 'getSlug'.($this->locale == "en"?"En":""))?'getSlug':'getName').($this->locale == "en"?"En":"");

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE && ($material->getSlugEn() == "" || $material->getSlug() == "")) {
            $this->output->writeln("bad content (".$material->$nameMethod().") slug : ".$material->getSlug().", en slug : ".$material->getSlugEn());
            return;
        }
        $arr = array(
                'loc' => $this->generateURL('ibtikar_goody_frontend_'.($type != 'product'?$material->getType().'_':'').'view',array('slug' => $this->locale == "en"?$material->getSlugEn():$material->getSlug(),'_locale'=>$this->locale)),
                'changefreq' => "monthly", //always, hourly, daily, weekly, monthly, yearly, never
                'priority' => "0.8",
                'title' =>$material->$nameMethod(),
                );


        if($material->getUpdatedAt()){
            $arr['lastmod'] = $material->getUpdatedAt()->format('Y-m-d');
        }
        $this->links[] = $arr;
    }


    protected function addLinkNormal($routePrefix, $params = array()) {
        $arr = array(

                'loc' => $this->generateURL($routePrefix.(substr($routePrefix, -1) == "_"?$this->locale:""), array_merge(array('_locale' => $this->locale),$params)),
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

//        $link = $record->addChild('xhtml:xhtml:link',null);

//        $link->addAttribute("rel","alternate");
//        $link->addAttribute("media","handheld");
//        $link->addAttribute("href",htmlspecialchars($array['loc']));

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

        $this->sitemapXml->addAttribute('encoding', 'UTF-8');
        $this->xmlOutputFile = $this->saveLocation;
    }

    private function generateURL($route, $params=array()){
      return $this->router->generate($route, $params,\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
