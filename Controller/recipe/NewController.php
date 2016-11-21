<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\recipe;

use Ibtikar\GlanceDashboardBundle\Controller\RecipeController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

class NewController extends RecipeController {

    protected function configureListParameters(Request $request) {
        parent::configureListParameters($request);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Recipe\List:new.html.twig");

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
        $renderingParams['roomName'] = $this->recipeStatus;
        return $renderingParams;
    }
}
