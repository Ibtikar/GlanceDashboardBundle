<?php

namespace Ibtikar\UserBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ibtikar\UserBundle\Document\UserDocumentLike;
use Ibtikar\UserBundle\Document\Document;
use Ibtikar\AppBundle\Document\Material;
use Ibtikar\AppBundle\Document\Comics;
use Ibtikar\UserBundle\Document\User;
use Ibtikar\AppBundle\Document\Questionnaire;
use Ibtikar\AppBundle\Document\Poll;
use Ibtikar\BackendBundle\Document\ActionLog;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class UserLike {

    /** @var DocumentManager $dm */
    private $dm;

    private $container;


    /**
     * @param ManagerRegistry $mr
     */
    public function __construct(ManagerRegistry $mr, $container) {
        $this->dm = $mr->getManager();
        $this->container = $container;

    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Document $document
     * @param User $user
     * @return boolean
     */
    public function like(Document $document, User $user) {
        $UserDocumentLike = $this->dm->getRepository('IbtikarUserBundle:UserDocumentLike')->getUserDocumentLike($document->getId(), $user->getId());
        if ($UserDocumentLike) {
            return false;
        }
        $documentLike = new UserDocumentLike();
        $documentLike->setUser($user);
        $documentLike->setDocument($document);
        $this->dm->persist($documentLike);
        if(strpos(get_class($document), 'Material') !== false || strpos(get_class($document), 'Comics') !== false) {
            $user->setLikesCount($user->getLikesCount() + 1);
        }
        if ($document instanceof Comics) {
            $author = $document->getAuthor();
            if($author) {
                $author->setTotalDocumentsLikesCount($author->getTotalDocumentsLikesCount() + 1);
            }
        }
        if ($document instanceof Material) {
            foreach ($document->getAuthor() as $author) {
                $author->setTotalDocumentsLikesCount($author->getTotalDocumentsLikesCount() + 1);
            }
        }
        $document->setNoOfLikes($document->getNoOfLikes() + 1);
        if ($document instanceof Material || $document instanceof Comics || $document instanceof Questionnaire || $document instanceof Poll) {
            if ($document instanceof Comics) {
                if ($document->getType() == '4-comics') {
                    $type = ActionLog::$types['comics'];
                } else if ($document->getType() == '1-album') {
                    $type = ActionLog::$types['album'];
                } else if ($document->getType() == '2-image') {
                    $type = ActionLog::$types['image'];
                } else if ($document->getType() == '3-video') {
                    $type = ActionLog::$types['video'];
                }
            } elseif ($document instanceof Questionnaire) {
                $type = ActionLog::$types['questionnaire'];
            } elseif ($document instanceof Poll) {
                $type = ActionLog::$types['poll'];
            } elseif ($document instanceof Material) {
                if ($document->getMaterialType() == '1-news') {
                    $type = ActionLog::$types['news'];
                } else {
                    $type = ActionLog::$types['article'];
                }
            }
            $this->container->get('action_logging')->log($document, ActionLog::$actions['like'], $type);
        }
        $this->dm->flush();
        return true;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Document $document
     * @param User $user
     * @return boolean
     */
    public function unlike(Document $document, User $user) {
        $userDocumentLike = $this->dm->getRepository('IbtikarUserBundle:UserDocumentLike')->getUserDocumentLike($document->getId(), $user->getId());
        if ($userDocumentLike) {
            $userDocumentLike->delete($this->dm, $user);
            $document->setNoOfLikes($document->getNoOfLikes() - 1);
            if ($document instanceof Material || $document instanceof Comics || $document instanceof Questionnaire || $document instanceof Poll) {
                if ($document instanceof Comics) {
                    if ($document->getType() == '4-comics') {
                        $type = ActionLog::$types['comics'];
                    } else if ($document->getType() == '1-album') {
                        $type = ActionLog::$types['album'];
                    } else if ($document->getType() == '2-image') {
                        $type = ActionLog::$types['image'];
                    } else if ($document->getType() == '3-video') {
                        $type = ActionLog::$types['video'];
                    }
                } elseif ($document instanceof Questionnaire) {
                    $type = ActionLog::$types['questionnaire'];
                } elseif ($document instanceof Poll) {
                    $type = ActionLog::$types['poll'];
                } elseif ($document instanceof Material) {
                    if ($document->getMaterialType() == '1-news') {
                        $type = ActionLog::$types['news'];
                    } else {
                        $type = ActionLog::$types['article'];
                    }
                }
                $this->container->get('action_logging')->log($document, ActionLog::$actions['unlike'], $type);
            }
            if (strpos(get_class($document), 'Material') !== false || strpos(get_class($document), 'Comics') !== false) {
                $user->setLikesCount($user->getLikesCount() - 1);
            }
            if ($document instanceof Comics) {
                $author = $document->getAuthor();
                if($author) {
                    $author->setTotalDocumentsLikesCount($author->getTotalDocumentsLikesCount() - 1);
                }
            }
            if ($document instanceof Material) {
                foreach ($document->getAuthor() as $author) {
                    $author->setTotalDocumentsLikesCount($author->getTotalDocumentsLikesCount() - 1);
                }
            }
            $this->dm->flush();
            return true;
        }
        return false;
    }

}
