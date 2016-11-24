<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\recipe;

use Ibtikar\GlanceDashboardBundle\Controller\RecipeController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

class AutopublishController extends RecipeController
{

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'recipe' . strtolower($calledClassName[1]);
        $this->recipeStatus = Recipe::$statuses['autopublish'];
    }

    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }



}
