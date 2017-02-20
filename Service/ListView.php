<?php

namespace Ibtikar\GlanceDashboardBundle\Service;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author Maisara Khedr
 */
class ListView {

    private $securityContext;

    private $fields;
    private $actions;
    private $bulkActions;
    private $restorable;
    private $listType;
    private $defaultSortBy;
    private $defaultSortOrder;
    private $bundlePrefix;
    private $listQueryBuilder;
    private $breadcrumbs;
    private $template;


    public function __construct($container) {
        $this->fields = array();
        $this->actions = array ();
        $this->bulkActions = array ();
        $this->breadcrumbs = array ();
        $this->restorable = false;
        $this->template = NULL;

        $this->securityContext = $container->get('security.authorization_checker');
    }

    /**
     *
     * @param type $title to be translated and displayed as a header of the column
     * @param type $propertyName used to get the value of the property with this name from the document class
     * @param type $type used to differnt ways of display on list view. possible values (string,date,computed)
     * @param type $tooltip text that will be translated and displayed as tooltip in each row for this column
     * @param type $getterArguments arguments to be passed to the getter method in case of computed type
     * @param type $isSortable fklag to determine if the field is sortable or not
     * @param type $isClickable fklag to determine if the field is sortable or not
     * @return \Ibtikar\BackendBundle\Service\ListView
     */
    public function addField($title,$propertyName,$type="string",$sortOrderType="normal",$tooltip=null,$getterArguments=null,$isSortable=true,$isClickable=false,$className="",$document="document") {
        $field = new \stdClass();
        $field->title = $title;
        $field->propertyName = $propertyName;
        $field->type = $type;
        $field->document = $document;
        $field->tooltip = $tooltip;
        $field->arguments = $getterArguments;
        $field->isSortable = $isSortable;
        $field->isClickable = $isClickable;
        $field->class = $className;
        $field->sortOrderType = $sortOrderType;
        $this->fields[$propertyName] = $field;
        return $this;
    }

    public function getFields() {
        return $this->fields;
    }

    public function getActions() {
        return $this->actions;
    }

    public function hasAction($actionName) {
        if(in_array($actionName, $this->actions)) {
            return TRUE;
        }
        return FALSE;
    }

    public function addAction($action) {
        $this->actions[] = $action;
    }

    public function hasBulkActions($listName) {
        if(count($this->bulkActions) == 0) {
            return FALSE;
        }
        if($this->securityContext->isGranted('ROLE_ADMIN')) {
            return TRUE;
        }

        if (in_array('Favorite', $this->bulkActions) || in_array('Unfavorite', $this->bulkActions) ) {
            return TRUE;
        }
        foreach ($this->bulkActions as $action) {
            if(in_array($action, array("Activate","Deactivate"))){
                $action = "Activate_Deactivate";
            }
            else if(in_array($action, array("Publish","Unpublish"))){
                $action = "Publish_Unpublish";
            }
            if($this->securityContext->isGranted('ROLE_'.  strtoupper($listName).'_'.strtoupper($action))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function hasActionsColumn($listName) {
        $actions = array_diff($this->actions, array("Search"));
        if(count($actions) == 0) {
            return FALSE;
        }

        if ($this->securityContext->isGranted('ROLE_ADMIN') && $listName != 'SubCategory') {
            return TRUE;
        }
        foreach ($actions as $action) {
            if ($action == 'ManageOne' && $listName = 'Category') {
                $action = 'Manage';
                $listName = 'subcategory';
            }
            if ($listName == 'Poll' && $action == 'AutoPublish') {
                if (in_array($action, array("Edit", "Delete", "Activate_Deactivate", "Publish_Unpublish", 'Publish', 'AutoPublish', 'Backward', "Assign", "AssignTo", "History", "Reassign", "Show", "Forward", 'ViewOne', 'Unpublish', 'PublishControl', 'AutoPublishControl', 'ManageOne', 'Manage', 'StopResume', 'Viewcomment', 'ViewPlaces', 'AddPlaces','Resendmail')) && $this->securityContext->isGranted('ROLE_' . strtoupper($listName) . '_' . strtoupper('autopublished'))) {
                    return TRUE;
                }
            }
            if ($listName == 'Poll' && $action == 'AutoPublishControl') {
                if (in_array($action, array("Edit", "Delete", "Activate_Deactivate", "Publish_Unpublish", 'Publish', 'AutoPublish', 'Backward', "Assign", "AssignTo", "History", "Reassign", "Show", "Forward", 'ViewOne', 'Unpublish', 'PublishControl', 'AutoPublishControl', 'ManageOne', 'Manage', 'StopResume', 'Viewcomment', 'ViewPlaces', 'AddPlaces','Resendmail')) && $this->securityContext->isGranted('ROLE_' . strtoupper($listName) . '_' . strtoupper('autopublishedControl'))) {
                    return TRUE;
                }
            }
            if (in_array($action, array("Approve","Reject","Edit", "Delete", "Activate_Deactivate", "Publish_Unpublish", 'Publish', 'AutoPublish', 'Backward', "Assign", "AssignTo", "History", "Reassign", "Show", "Forward", 'ViewOne', 'Unpublish', 'PublishControl', 'AutoPublishControl', 'ManageOne', 'Manage', 'StopResume', 'Viewcomment', 'ViewPlaces', 'AddPlaces','Resendmail','Favorite_Unfavorite','ChangeStatus')) && $this->securityContext->isGranted('ROLE_' . strtoupper($listName) . '_' . strtoupper($action))) {
                return TRUE;
            }
            if ($action == "Addcontact" && $this->securityContext->isGranted('ROLE_CONTACT_CREATE')) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getOppositeOrderDirection($fieldSortOrderType,$direction) {
        if ($fieldSortOrderType == "reversed") {
            return ($direction == "asc") ? "desc" : "asc";
        }
        return $direction;
    }

    public function getBulkActions() {
        return $this->bulkActions;
    }

    public function addBulkAction($bulkAction) {
        $this->bulkActions[] = $bulkAction;
    }

    public function getRestorable() {
        return $this->restorable;
    }

    public function getListType() {
        return $this->listType;
    }

    public function getDefaultSortBy() {
        return $this->defaultSortBy;
    }

    public function getDefaultSortOrder() {
        return $this->defaultSortOrder;
    }

    public function getListQueryBuilder() {
        return $this->listQueryBuilder;
    }

    public function getBreadcrumbs() {
        return $this->breadcrumbs;
    }

    public function getTemplate() {
        return $this->template;
    }

    public function setBundlePrefix($prefix='ibtikar_glance_dashboard_') {
        $this->bundlePrefix = $prefix;
        return $this;
    }

    public function getBundlePrefix() {
        return $this->bundlePrefix;
    }

    public function setTemplate($template) {
        $this->template = $template;
        return $this;
    }

    public function setFields($fields) {
        $this->fields = $fields;
        return $this;
    }

    public function setActions($actions) {
        $this->actions = $actions;
        return $this;
    }

    public function setBulkActions($bulkActions) {
        $this->bulkActions = $bulkActions;
        return $this;
    }

    public function setRestorable($restorable) {
        $this->restorable = $restorable;
        return $this;
    }

    public function setListType($listType) {
        $this->listType = $listType;
        return $this;
    }

    public function setDefaultSortBy($defaultSortBy) {
        $this->defaultSortBy = $defaultSortBy;
        return $this;
    }

    public function setDefaultSortOrder($defaultSortOrder) {
        $this->defaultSortOrder = $defaultSortOrder;
        return $this;
    }

    public function setListQueryBuilder($listQueryBuilder) {
        $this->listQueryBuilder = $listQueryBuilder;
        return $this;
    }

    public function setBreadcrumbs($breadcrumbs) {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }

}
