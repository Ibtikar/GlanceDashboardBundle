<?php

namespace Ibtikar\GlanceDashboardBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;


class SessionLanguageListener {

    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var routeCollection \Symfony\Component\Routing\RouteCollection
     */
    private $routeCollection;

    /**
     * @var string
     */
    private $defaultLocale;
    
    /**
     * @var string
     */
    private $container;

    /**
     * @var array
     */
    private $supportedLocales;

    /**
     * @var string
     */
    private $localeRouteParam;

    public function __construct($container,RouterInterface $router, $defaultLocale = 'ar', array $supportedLocales = array('en'), $localeRouteParam = '_locale') {
        $this->router = $router;
        $this->routeCollection = $router->getRouteCollection();
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = $supportedLocales;
        $this->localeRouteParam = $localeRouteParam;
        $this->container=$container;
    }

    public function isLocaleSupported($locale) {
        return in_array($locale, $this->supportedLocales);
    }

    public function onResponse(FilterResponseEvent $event) {


        if (\Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $locale = $request->get('_locale');
        if (strpos($request->getRequestUri(), 'backend')) {
            $session->set('_locale', $this->defaultLocale);

            $response = $event->getResponse();

            $response->headers->setCookie(new Cookie('_locale', $this->defaultLocale));
            return;
        }

        if ($locale && $this->isLocaleSupported($locale)) {
            $session->set('_locale', $locale);
            $response = $event->getResponse();
            $response->headers->setCookie(new Cookie('_locale', $locale));
        }
        
//        $cookies = $request->cookies;
//        if ($cookies->has('_locale')) {
//            $locale = $cookies->get('_locale');
//        }
//        

        
//        $request = $event->getRequest();
//        if (strpos($request->getRequestUri(), 'logout')) {
//
//        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_STAFF')) {
//            $event->setResponse(new RedirectResponse("/".$locale));
//        }
//        }
        
        
        
    }



}
