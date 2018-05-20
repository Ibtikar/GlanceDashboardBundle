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
    private $translator;

    public function __construct($templating,$router,$translator)
    {
        $this->templating = $templating;
        $this->translator = $translator;

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
                 $locale='ar';
                 $newurl=$event->getRequest()->getRequestUri();

                if (strpos($event->getRequest()->getRequestUri(), '/en/')!==false) {
                    $locale='en';
                    $newurl= str_replace('/en/','/ar/', $event->getRequest()->getRequestUri());


                }else{
                    $locale='ar';
                    $newurl= str_replace('/ar/','/en/', $event->getRequest()->getRequestUri());


                }


//                \Doctrine\Common\Util\Debug::dump($this->context->getToken());
//                exit;
//                $event->setResponse(new RedirectResponse($this->router->generate('ibtikar_goody_frontend_error_404')));
                $this->translator->setLocale($locale);
                $event->getRequest()->setLocale($locale);
                $event->getRequest()->attributes->set('_locale', $locale);

                $response = $this->templating->renderResponse('IbtikarGoodyFrontendBundle:Exception:error.html.twig',array('locale'=>$locale,'url'=>$newurl));
                $event->setResponse($response);

            }
        }
        if ($event->getException() instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
            $response = $this->templating->renderResponse('IbtikarGlanceDashboardBundle:Exception:denied.html.twig');
            $event->setResponse($response);
        }
    }
}
