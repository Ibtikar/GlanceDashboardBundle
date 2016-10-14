<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\StaffListColumnsRepository")
 * @MongoDB\Index(keys={"listName"="asc"})
 */
class StaffListColumns extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\String
     */
    private $listName;

    /**
     * @MongoDB\String
     */
    private $columns;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceUMSBundle\Document\Staff")
     */
    protected $staff;


    public function __toString() {
        return (string) $this->listName;
    }



    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set listName
     *
     * @param string $listName
     * @return self
     */
    public function setListName($listName)
    {
        $this->listName = $listName;
        return $this;
    }

    /**
     * Get listName
     *
     * @return string $listName
     */
    public function getListName()
    {
        return $this->listName;
    }

    /**
     * Set columns
     *
     * @param string $columns
     * @return self
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Get columns
     *
     * @return string $columns
     */
    public function getColumns()
    {
        return $this->columns;
    }



    /**
     * Set staff
     *
     * @param Ibtikar\GlanceUMSBundle\Document\Staff $staff
     * @return self
     */
    public function setStaff(\Ibtikar\GlanceUMSBundle\Document\Staff $staff)
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * Get staff
     *
     * @return Ibtikar\GlanceUMSBundle\Document\Staff $staff
     */
    public function getStaff()
    {
        return $this->staff;
    }
}
