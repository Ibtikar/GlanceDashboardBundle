<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\rooms;

use Ibtikar\GlanceDashboardBundle\Controller\base\RoomController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

class NewsController extends RoomController {

    protected $roomName = 'news';
    protected function configureListParameters(Request $request) {
        parent::configureListParameters($request);
        if ($this->listStatus == 'list_new_recipe'){
//            $queryBuilder = $this->createQueryBuilder();
//                ->field('room')->equals($this->roomName)
//                ->field('status')->equals('new')
//                ->field('assignedTo')->equals(null)
//                ->field('deleted')->equals(false);
//            $this->listViewOptions->setActions(array ('Assign', 'Show'));
//            $this->listViewOptions->setDefaultSortBy("createdAt");
//            $this->listViewOptions->setDefaultSortOrder("desc");
        }
        else if ($this->listStatus == 'list_visitor_recipe'){
//            $queryBuilder = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                ->field('room')->equals($this->roomName)
//                ->field('status')->equals('new')
//                ->field('source')->equals('3-frontend-user')
//                ->field('assignedTo')->equals(null)
//                ->field('deleted')->equals(false);
//            $this->listViewOptions->setActions(array ('Assign', 'Show', 'Search'));
        }



        if(isset($queryBuilder))
            $this->listViewOptions->setListQueryBuilder($queryBuilder);
            $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Recipe\Rooms:news.html.twig");

    }

    public function listVisitorRecipeAction(Request $request){
        $this->listStatus = 'list_visitor_recipe';
        $this->listName = 'room_' . $this->roomName . '_' . $this->listStatus;
        return parent::listAction($request);
    }



    protected function doList(Request $request) {

        $renderingParams = parent::doList($request);
//        $renderingParams['newRecipeCount'] = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                                            ->field('room')->equals($this->roomName)
//                                            ->field('status')->equals('new')
//                                            ->field('source')->in(array('4-backend-user', '1-email', '2-Migration'))
//                                            ->field('assignedTo')->equals(null)
//                                            ->field('deleted')->equals(false)
//                                            ->getQuery()->execute()->count();
//        $renderingParams['visitorRecipeCount'] = $this->createQueryBuilder('IbtikarGlanceDashboardBundle:Recipe')
//                                            ->field('room')->equals($this->roomName)
//                                            ->field('status')->equals('new')
//                                            ->field('source')->equals('3-frontend-user')
//                                            ->field('assignedTo')->equals(null)
//                                            ->field('deleted')->equals(false)
//                                            ->getQuery()->execute()->count();
        $renderingParams['roomName'] = $this->roomName;
        return $renderingParams;
    }
}
