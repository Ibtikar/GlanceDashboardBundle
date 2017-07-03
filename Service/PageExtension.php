<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

class PageExtension extends \Twig_Extension {

    private $dm;

    public function __construct($mr) {
        $this->dm = $mr->getManager();
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction(
                    'get_page_data', array($this, 'getPagesData')
            ),
            new \Twig_SimpleFunction(
                    'get_page_data_by_name', array($this, 'getPagesDataByName')
            )
        );
    }

    public function getPagesData() {
        return $this->dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->findAll();
    }
    
    public function getPagesDataByName($name) {
   if($name=='articles'){
             $shortName= 'ArticleBannar';
  
   }else{
             $shortName= ucfirst($name).'Bannar';
  
   }
        return $this->dm->getRepository('IbtikarGlanceDashboardBundle:HomeBanner')->findOneBy(array('shortName'=>$shortName));
    }
    
   

}
