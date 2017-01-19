<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ibtikar\GlanceDashboardBundle\Document\Redirect as RedirectDocument;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class Redirect {

    /**
     * @var ContainerInterface
     */
    private $container;
    private $shortUrlBase;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, $shortUrlBase) {
        $this->container = $container;
        $this->shortUrlBase = $shortUrlBase;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event) {
        $requestedUrl = $event->getRequest()->getPathInfo();

        // The redirect listener should not work on all routes it should check for the route name before selecting from database`
        if (!in_array($this->container->get('request_stack')->getCurrentRequest()->get('_route'), array('ibtikar_goody_frontend_shorturl'))) {
            return;
        }

        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $redirect = $dm->getRepository('IbtikarGlanceDashboardBundle:Redirect')->findOneByOldUrl($requestedUrl);
        if ($redirect) {
            $redirect->setAccessCount($redirect->getAccessCount() + 1);
            $redirect->setLastAccessedAt(new \DateTime());
            $dm->flush();
            $redirectToUrl = $redirect->getRedirectToUrl();
            if($event->getRequest()->getScriptName() !== '/app.php') {
                $redirectToUrl = $event->getRequest()->getScriptName() . $redirect->getRedirectToUrl();
            }
            $event->setResponse(new RedirectResponse($redirectToUrl, $redirect->getStatusCode()));
        }
    }

    /**
     * @param string $url
     */
    public function removeRedirect($url) {
        $user = null;
        $token = $this->container->get('security.context')->getToken();
        if ($token && is_object($token->getUser())) {
            $user = $token->getUser();
        }
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        if ($this->container->isScopeActive('request')) {
        $scriptName = $this->container->get('request')->getScriptName();
        $url = str_replace($scriptName, '', $url);
        }
        $url= str_replace($this->shortUrlBase, '/', $url);
        $oldRedirects = $dm->getRepository('IbtikarGlanceDashboardBundle:Redirect')->findByOldUrl($url);
        foreach ($oldRedirects as $oldRedirect) {
            $oldRedirect->delete($dm, $user);
        }
        $dm->flush();
    }

    /**
     * @param string $oldUrl
     * @param string|null $redirectToUrl
     * @param boolean $ignoreOldUrlsUpdate
     */
    public function addPermanentRedirect($oldUrl, $redirectToUrl = null, $ignoreOldUrlsUpdate = false) {
        $this->addRedirect(301, $oldUrl, $redirectToUrl, $ignoreOldUrlsUpdate);
    }

    /**
     * @param string $oldUrl
     * @param string|null $redirectToUrl
     * @param boolean $ignoreOldUrlsUpdate set to true when you add redirect that will be removed like delete something that can be restored
     */
    public function addTemporaryRedirect($oldUrl, $redirectToUrl = null, $ignoreOldUrlsUpdate = false) {
        $this->addRedirect(302, $oldUrl, $redirectToUrl, $ignoreOldUrlsUpdate);
    }

    /**
     * @param int $statusCode
     * @param string $oldUrl
     * @param string|null $redirectToUrl
     * @param boolean $ignoreOldUrlsUpdate set to true when you add redirect that will be removed like delete something that can be restored
     */
    private function addRedirect($statusCode, $oldUrl, $redirectToUrl = null, $ignoreOldUrlsUpdate = false) {
        if (is_null($oldUrl)) {
            throw new \Exception('please set old url.');
        }
        if (is_null($redirectToUrl)) {
            $redirectToUrl = $this->container->get('router')->generate('ibtikar_goody_frontend_homepage');
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request) {
            $scriptName = $request->getScriptName();
            $redirectToUrl = str_replace($scriptName, '', $redirectToUrl);
            $oldUrl = str_replace($scriptName, '', $oldUrl);
        }
        $oldUrl= str_replace($this->shortUrlBase, '/', $oldUrl);
        $redirectToUrl= str_replace($this->shortUrlBase, '/', $redirectToUrl);
        if ($oldUrl === $redirectToUrl) {
            throw new \Exception('You can not set a redirect loop for the url ' . $redirectToUrl);
        }
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $redirectRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Redirect');
        $checkOldRedirect = $redirectRepo->findOneByOldUrl($oldUrl);
        if ($checkOldRedirect) {
            throw new \Exception('Old url ' . $oldUrl . ' is already redirecting to another url ' . $checkOldRedirect->getRedirectToUrl() . ' please remove it first.');
        }
        if (!$ignoreOldUrlsUpdate) {
            $oldRedirects = $redirectRepo->findByRedirectToUrl($oldUrl);
            if (count($oldRedirects) > 0) {
                foreach ($oldRedirects as $oldRedirect) {
                    // edit all old redirects to the old url to point to the new url
                    if($oldRedirect->getOldUrl() !== $redirectToUrl) {
                        $oldRedirect->setRedirectToUrl($redirectToUrl);
                    } else {
                        // delete any wrong old redirect for the new url that will redirect to the same new url to prevent infinite redirect loop
                        $oldRedirect->delete($dm);
                    }
                }
            }
        }
        $redirect = new RedirectDocument();
        $redirect->setStatusCode($statusCode);
        $redirect->setOldUrl($oldUrl);
        $redirect->setRedirectToUrl($redirectToUrl);
        $dm->persist($redirect);
        $dm->flush();
    }

}
