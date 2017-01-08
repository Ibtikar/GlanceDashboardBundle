<?php

namespace Ibtikar\GlanceDashboardBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;

class SessionLanguageListener implements EventSubscriberInterface
{

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
     * @var array
     */
    private $supportedLocales;

    /**
     * @var string
     */
    private $localeRouteParam;

    public function __construct(RouterInterface $router, $defaultLocale = 'ar', array $supportedLocales = array('en'), $localeRouteParam = '_locale')
    {
        $this->router = $router;
        $this->routeCollection = $router->getRouteCollection();
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = $supportedLocales;
        $this->localeRouteParam = $localeRouteParam;
    }

    public function isLocaleSupported($locale)
    {
        return in_array($locale, $this->supportedLocales);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        if (\Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $locale = $request->get('_locale');
        if (strpos($request->getRequestUri(), 'backend')) {
            $session->set('_locale', $this->defaultLocale);
            return;
        }

        if ($locale && $this->isLocaleSupported($locale)) {
            $session->set('_locale', $locale);
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }
}
