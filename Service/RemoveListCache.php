<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RemoveListCache {

    public function onKernelResponse(FilterResponseEvent $event) {
        if ($event->getRequest()->isXmlHttpRequest() && strpos($event->getRequest()->getRequestUri(), '/backend') !== false) {
            $event->getResponse()->headers->set('Cache-Control', 'private, max-age=0, must-revalidate, no-store');
        }
    }

}
