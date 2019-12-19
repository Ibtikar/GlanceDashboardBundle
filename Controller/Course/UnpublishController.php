<?php

namespace Ibtikar\GlanceDashboardBundle\Controller\Course;

use Ibtikar\GlanceDashboardBundle\Controller\CourseController;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\GlanceDashboardBundle\Document\Course;

class UnpublishController extends CourseController
{
    protected $status = 'unpublish';

    public function __construct()
    {
        parent::__construct();
        $calledClassName = explode('\\', $this->calledClassName);
        $this->calledClassName = 'Course' . strtolower($calledClassName[1]);
        $this->recipeStatus = Course::$statuses['unpublish'];
    }


    protected function configureListParameters(Request $request)
    {
        parent::configureListParameters($request);
        $this->listViewOptions->setActions(array('Edit', 'Delete', 'Publish', 'ViewOne','ViewAnswers'));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setDefaultSortBy("createdAt");
        $this->listViewOptions->setDefaultSortOrder("desc");
    }
    public function listUnpublishCourseAction(Request $course)
    {

        $this->listStatus = 'list_unpublish_course';
        $this->listName = 'course' . $this->status . '_' . $this->listStatus;
        return parent::listAction($course);
    }

    public function changeListUnpublishCourseColumnsAction(Request $course)
    {
        $this->listStatus = 'list_unpublish_course';
        $this->listName = 'course' . $this->status . '_' . $this->listStatus;
        return parent::changeListColumnsAction($course);
    }



}
