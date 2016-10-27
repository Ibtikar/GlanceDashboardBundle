<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


class NotFoundExceptionListener {

    private $templating;
    public function __construct($templating) {
        $this->templating = $templating;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        if ($event->getException() instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {

            if(strpos($event->getRequest()->getRequestUri(),'backend')){
                $response = $this->templating->renderResponse('IbtikarGlanceDashboardBundle:Exception:error.html.twig');
                $event->setResponse($response);
            }
        }
        if ($event->getException() instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                $response = $this->templating->renderResponse('IbtikarGlanceDashboardBundle:Exception:denied.html.twig');
                $event->setResponse($response);

        }
    }

}
