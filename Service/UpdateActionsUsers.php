<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UpdateActionsUsers implements EventSubscriber {
    /*
     * @var ContainerInterface
     */

    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @return Ibtikar\UserBundle\Document\User|null
     */
    private function getLoggedinUser() {
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token) {
            $user = $token->getUser();
            if (is_object($user)) {
                return $user;
            }
        }
    }

    /**
     * @return array
     */
    public function getSubscribedEvents() {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $document = $args->getDocument();

        if (is_subclass_of($document, 'Ibtikar\GlanceDashboardBundle\Document\Document')) {
            $user = $this->getLoggedinUser();
            if ($user && is_a($user, 'Ibtikar\GlanceUMSBundle\Document\User')) {
                $document->setCreatedBy($user);
            }
            if($document->getCreatedAt() === null) {
                $document->setCreatedAt(new \DateTime());
            }
            $document->updateReferencesCounts(1);
        }
    }

    /**
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $document = $args->getDocument();
        if (php_sapi_name() !== 'cli') {
        $route = $this->container->get('request_stack')->getCurrentRequest()->get('_route');
        if (is_subclass_of($document, 'Ibtikar\GlanceDashboardBundle\Document\Document')) {
            if($route != 'app_view' && $route != 'get_slug_details'){
                $user = $this->getLoggedinUser();
                if ($user && is_a($user, 'Ibtikar\GlanceUMSBundle\Document\User')) {
                    $document->setUpdatedBy($user);
                }
                $document->setUpdatedAt(new \DateTime());
                $dm = $args->getDocumentManager();
                $class = $dm->getClassMetadata(get_class($document));
                $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $document);
            }
        }
        }
    }

}
