<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class BaseEmail {

    private $templating;

    /**
     * @var array
     */
    private $baseImages = array();

    public function __construct($templating) {
        $this->templating = $templating;
        // set the template images correct path
        $images = array(
            'logo-header.jpg',
            'social-twitter.jpg',
            'social-facebook.jpg',
            'social-telegram.jpg',
            'social-skype.jpg',
            'social-linked.jpg',
            'social-yahoo.jpg',
            'social-google.jpg',
            'social-instagram.jpg',
            'social-youtube.jpg',
            'icon-mail.jpg',
            'image-560x220.jpg',
            'tw.jpg',
            'linkedin.jpg',
            'fb.jpg',
            'google.jpg',
            'facebook.jpg',
            'twitter.jpg',
            'youtube.jpg',
            'instagram.jpg',
            'yahoo.jpg',
            'ibtikar.jpg'
        );
        foreach ($images as $image) {
//            $path = __DIR__ . '/../../../../web/email-images/' . $image;
//            $type = pathinfo($path, PATHINFO_EXTENSION);
//            $data = file_get_contents($path);
//            $baseImages[$image] = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $this->baseImages[$image] = '/email-images/' . $image;
        }
    }

    /**
     * @param string $personTitle
     * @param boolean $showMessage
     * @param boolean $showHeaderContent
     * @return string
     */
    public function getBaseRender($personTitle = 'الأستاذ/ الأستاذة', $showMessage = true, $showHeaderContent = false,$showSocialLinks = true) {
        return $this->templating->render('IbtikarGlanceDashboardBundle:Email:email-base.html.twig', array(
                    'baseImages' => $this->baseImages,
                    'personTitle' => $personTitle,
                    'showMessage' => $showMessage,
                    'showHeaderContent' => $showHeaderContent,
                    'showSocialLinks' => $showSocialLinks
        ));
    }

    /**
     * @param string $socialNetwork valid types are twitter, linkedin, facebook, google and yahoo
     * @param string $url
     * @return string
     */
    public function getSocialLinkRender($socialNetwork, $url) {
        switch ($socialNetwork) {
            case 'twitter':
                $socialImage = $this->baseImages['tw.jpg'];
                $socialNetworkAlt = 'TW';
                break;
            case 'linkedin':
                $socialImage = $this->baseImages['linkedin.jpg'];
                $socialNetworkAlt = 'LN';
                break;
            case 'facebook':
                $socialImage = $this->baseImages['fb.jpg'];
                $socialNetworkAlt = 'FB';
                break;
            case 'google':
                $socialImage = $this->baseImages['google.jpg'];
                $socialNetworkAlt = 'GO';
                break;
            case 'yahoo':
                $socialImage = $this->baseImages['yahoo.jpg'];
                $socialNetworkAlt = 'YH';
                break;
        }
        return $this->templating->render('IbtikarGlanceDashboardBundle:Email:email-social-link.html.twig', array('socialImage' => $socialImage, 'socialNetworkAlt' => $socialNetworkAlt, 'url' => $url));
    }

}
