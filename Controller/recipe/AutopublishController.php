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

        protected function configureListColumns()
    {
        $this->allListColumns = array(
            "title" => array("searchFieldType" => "input"),
            "titleEn" => array("type" => "translated"),
            "country" => array("isSortable" => false),
            "createdBy" => array("isSortable" => false),
            "publishedBy" => array("isSortable" => false),
            "createdAt" => array("type" => "date"),
            "autoPublishDate" => array("type" => "date"),
            "updatedAt" => array("type" => "date"),
            "chef" => array("isSortable" => false),
        );
        $this->defaultListColumns = array(
            "title",
            "autoPublishDate",
            "chef",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setDefaultSortBy("autoPublishDate");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }



}
