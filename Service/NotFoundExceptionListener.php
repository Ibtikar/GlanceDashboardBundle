<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NotFoundExceptionListener
{

    private $templating;
    private $router;

    public function __construct($templating,$router)
    {
        $this->templating = $templating;
        $this->router = $router;

    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        if ($event->getException() instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
// \Doctrine\Common\Util\Debug::dump($event->getException());
// exit;
            if (strpos($event->getRequest()->getRequestUri(), 'backend')) {
                $response = $this->templating->renderResponse('IbtikarGlanceDashboardBundle:Exception:error.html.twig');
                $event->setResponse($response);
            } else {
//                \Doctrine\Common\Util\Debug::dump($this->context->getToken());
//                exit;
//                $event->setResponse(new RedirectResponse($this->router->generate('ibtikar_goody_frontend_error_404')));
                $response = $this->templating->renderResponse('IbtikarGoodyFrontendBundle:Exception:error.html.twig');
                $event->setResponse($response);

            }
        }
        if ($event->getException() instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
            $response = $this->templating->renderResponse('IbtikarGlanceDashboardBundle:Exception:denied.html.twig');
            $event->setResponse($response);
        }
    }
}
