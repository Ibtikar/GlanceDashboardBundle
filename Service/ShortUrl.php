<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\GlanceDashboardBundle\Document\ShortenedUrl;
use Ibtikar\GlanceDashboardBundle\Service\Redirect;
use Ibtikar\GlanceDashboardBundle\Document\Slug;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class ShortUrl {

    /** @var String $shortCodeCharacters check the next link for more details about why this string https://github.com/delight-im/ShortURL/blob/master/PHP/ShortURL.php * */
    private $shortCodeCharacters = '23456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';

    /** @var Integer $shortCodeLength like the old sabq slug length */
    private $shortCodeLength = 6;

    /** @var DocumentManager $dm */
    private $dm;

    /** @var Redirect $redirect */
    private $redirect;

    /** @var String $shortUrlBase */
    private $shortUrlBase;

    private $container;

    public function __construct(ManagerRegistry $mr, Redirect $redirect, $shortUrlBase, $container) {
        $this->dm = $mr->getManager();
        $this->redirect = $redirect;
        $this->shortUrlBase = $shortUrlBase;
        $this->container = $container;
    }

    /**
     * @param string $url
     * @return string
     */
    public function extractRelativeUrl($url) {
        $urlParts = parse_url($url);
        $relativeUrl = str_replace('/app_dev.php', '', $urlParts['path']);
        if (isset($urlParts['query']) && $urlParts['query']) {
            $relativeUrl .= '?' . $urlParts['query'];
        }
        return $relativeUrl;
    }

    private function changeUrlToSiteDomain($url){
//        $urlParts = parse_url($url);
//        $url = str_replace($urlParts['host'], $this->container->getParameter('site_domain'), $url);
        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getShortUrl($url, $locale='ar') {
        $relativeUrl = $this->extractRelativeUrl($url);
        $ShortenedUrl = $this->dm->getRepository('IbtikarGlanceDashboardBundle:ShortenedUrl')->findOneByUrl($relativeUrl);
        if ($ShortenedUrl) {
            return $this->changeUrlToSiteDomain($this->shortUrlBase . $ShortenedUrl->getShortCode());
        }
        return $this->changeUrlToSiteDomain($this->generateShortUrl($relativeUrl, $locale));
    }

    /**
     * @param array $urls
     * @return array $shortenedUrls
     */
    public function getShortUrls(array $urls) {
        $shortenedUrls = array();
        if (count($urls) > 0) {
            $relativeUrls = array();
            foreach ($urls as $url) {
                $relativeUrls [] = $this->extractRelativeUrl($url);
            }
            $shortenedUrlsObjects = $this->dm->getRepository('IbtikarGlanceDashboardBundle:ShortenedUrl')->findBy(array('url' => array('$in' => $relativeUrls)));
            foreach ($shortenedUrlsObjects as $shortenedUrlObject) {
                $shortenedUrls[$shortenedUrlObject->getUrl()] = $this->shortUrlBase . $shortenedUrlObject->getShortCode();
            }
            foreach($relativeUrls as $relativeUrl) {
                if(!isset($shortenedUrls[$relativeUrl])) {
                    $shortenedUrls[$relativeUrl] = $this->generateShortUrl($relativeUrl);
                }
            }
        }
        return $shortenedUrls;
    }

    /**
     * @param string $url
     * @return string
     */
    private function generateShortUrl($url, $locale='ar') {
        $shortCodeValid = false;
        $shortCode = '';
        do {
            $shortCode = $this->generateShortCode();
            $existingSlug = $this->dm->getRepository('IbtikarGlanceDashboardBundle:Slug')->getDocumentBySlug($shortCode);

            if ($existingSlug == 0) {
                $shortCodeValid = true;
            }
        } while (!$shortCodeValid);
        $setSlugWithLocale = 'setSlug'.ucfirst($locale);
        $slug = new Slug();
        $slug->$setSlugWithLocale($shortCode);
        $slug->setType(Slug::$TYPE_SHORTURL);
        $slug->setPublish(true);
        $this->dm->persist($slug);
        $this->dm->flush($slug);
        $shortenedUrl = new ShortenedUrl();
        $shortenedUrl->setUrl($url);
        $shortenedUrl->setShortCode($shortCode);
        $this->dm->persist($shortenedUrl);
        $this->dm->flush($shortenedUrl);
        $this->redirect->addTemporaryRedirect("/$shortCode", $url);
        return "$this->shortUrlBase$shortCode";
    }

    /**
     * @return string
     */
    private function generateShortCode() {
        return str_shuffle(
                substr(str_shuffle($this->shortCodeCharacters), 0, $this->shortCodeLength)
        );
    }

}
