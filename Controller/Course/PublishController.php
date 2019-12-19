<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Course;

use Ibtikar\GlanceDashboardBundle\Controller\CourseController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Course;

class PublishController extends CourseController
{
    protected $status = 'publish';

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'course' . strtolower($calledClassName[1]);
        $this->recipeStatus = Course::$statuses['publish'];
    }


    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array(),
            "createdAt" => array("type" => "date"),
            "questionsCount" => array(),
            "slug" => array('type' => 'slug'),
            'noOfAnswer' => array(),
        );
        $this->defaultListColumns = array(
            "name",
            "createdAt",
            "questionsCount",
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");

    }


    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setActions(array('Edit', 'Delete', 'ViewOne','unPublish','ViewAnswers'));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }
    public function listPublishCourseAction(Request $course)
    {

        $this->listStatus = 'list_publish_course';
        $this->listName = 'course' . $this->status . '_' . $this->listStatus;
        return parent::listAction($course);
    }

    public function changeListPublishCourseColumnsAction(Request $course)
    {
        $this->listStatus = 'list_publish_course';
        $this->listName = 'course' . $this->status . '_' . $this->listStatus;
        return parent::changeListColumnsAction($course);
    }



}
