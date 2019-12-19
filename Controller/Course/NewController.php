<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Course;

use Ibtikar\GlanceDashboardBundle\Controller\CourseController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Course;

class NewController extends CourseController
{

    protected $status = 'new';

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'course' . strtolower($calledClassName[1]);
        $this->recipeStatus = Course::$statuses['new'];
    }

    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne'));
        $this->listViewOptions->setBulkActions(array("Delete"));

        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }

    public function listNewCourseAction(Request $course)
    {

        $this->listStatus = 'list_new_course';
        $this->listName = 'course' . $this->status . '_' . $this->listStatus;
        return parent::listAction($course);
    }

    public function changeListNewCourseColumnsAction(Request $course)
    {
        $this->listStatus = 'list_new_course';
        $this->listName = 'course' . $this->status . '_' . $this->listStatus;
        return parent::changeListColumnsAction($course);
    }
}
