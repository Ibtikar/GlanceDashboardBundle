<?php

namespace Ibtikar\GlanceDashboardBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LanguageChangeListener {
    /* @var $acceptedLocales array */

    private $acceptedLocales;

    /* @var $defaultLocale string */
    private $defaultLocale;

    public function __construct(array $acceptedLocales, $defaultLocale) {
        $this->acceptedLocales = $acceptedLocales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function changeRequestLocale(GetResponseEvent $event) {
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $event->getRequest();
        $session = $request->getSession();
        if (strpos($request->getRequestUri(), 'backend')) {
            $session->set('_locale', $this->defaultLocale);
            $request->setLocale($session->get('_locale', $this->defaultLocale));
            return;
        }
        if (!$request->hasPreviousSession()) {
            return;
        }

        $locale = $request->query->get('_locale');
        if ($locale) {
            $session->set('_locale', $locale);
        }
        $request->setLocale($session->get('_locale', $this->defaultLocale));
    }

}
