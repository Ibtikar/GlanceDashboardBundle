<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document
 */
class Location extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\String
     */
    private $page;

    /**
     * @MongoDB\String
     */
    private $section;


    /**
     * @MongoDB\Int
     */
    private $maxNumberOfMaterials;

    /**
     * news, article, comics => , image, video, imageAlbum, videoAlbums
     * @MongoDB\Hash
     */
    private $type = array();


    /**
     * @MongoDB\Hash
     */
    private $sourceCategories = array();


    /**
     * @MongoDB\Boolean
     */
    private $requiredCoverImage = false;

    /**
     * @MongoDB\Boolean
     */
    private $isSelectable = true;

    /**
     * @MongoDB\Boolean
     */
    private $checkedByDefault = false;

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
     * Set page
     *
     * @param string $page
     * @return self
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get page
     *
     * @return string $page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set section
     *
     * @param string $section
     * @return self
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get section
     *
     * @return string $section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set maxNumberOfMaterials
     *
     * @param int $maxNumberOfMaterials
     * @return self
     */
    public function setMaxNumberOfMaterials($maxNumberOfMaterials)
    {
        $this->maxNumberOfMaterials = $maxNumberOfMaterials;
        return $this;
    }

    /**
     * Get maxNumberOfMaterials
     *
     * @return int $maxNumberOfMaterials
     */
    public function getMaxNumberOfMaterials()
    {
        return $this->maxNumberOfMaterials;
    }

    /**
     * Set type
     *
     * @param Hash $type
     * @return self
     */
    public function setType($type)
    {
        foreach ($type as $item) {
            $this->type[$item] = $item;
        }
        return $this;
    }

    /**
     * Get type
     *
     * @return Hash $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sourceCategories
     *
     * @param hash $sourceCategories
     * @return self
     */
    public function setSourceCategories($sourceCategories)
    {
        foreach ($sourceCategories as $category) {
            $this->sourceCategories[$category] = $category;
        }
        return $this;
    }

    /**
     * Get sourceCategories
     *
     * @return hash $sourceCategories
     */
    public function getSourceCategories()
    {
        return $this->sourceCategories;
    }


    /**
     * Set requiredCoverImage
     *
     * @param boolean $requiredCoverImage
     * @return self
     */
    public function setRequiredCoverImage($requiredCoverImage)
    {
        $this->requiredCoverImage = $requiredCoverImage;
        return $this;
    }

    /**
     * Get requiredCoverImage
     *
     * @return boolean $requiredCoverImage
     */
    public function getRequiredCoverImage()
    {
        return $this->requiredCoverImage;
    }

    /**
     * Set isSelectable
     *
     * @param boolean $isSelectable
     * @return self
     */
    public function setIsSelectable($isSelectable)
    {
        $this->isSelectable = $isSelectable;
        return $this;
    }

    /**
     * Get isSelectable
     *
     * @return boolean $isSelectable
     */
    public function getIsSelectable()
    {
        return $this->isSelectable;
    }

    /**
     *
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     *
     * @param \Ibtikar\UserBundle\Document\User $user
     * @param \Ibtikar\UserBundle\Document\User $date in case the publish at date is set for autopublish material
     * @return \Ibtikar\AppBundle\Document\PublishLocation
     */
    public function getPublishedLocationObject(\Ibtikar\GlanceUMSBundle\Document\User $user, $date = null) {

        $publishLocation = new PublishLocation();

        $publishLocation->setPage($this->getPage())
                ->setPublishedAt(is_null($date)?new \DateTime():$date)
                ->setPublishedBy($user)
                ->setSection($this->getSection());

        return $publishLocation;
    }



    /**
     * Set checkedByDefault
     *
     * @param boolean $checkedByDefault
     * @return self
     */
    public function setCheckedByDefault($checkedByDefault)
    {
        $this->checkedByDefault = $checkedByDefault;
        return $this;
    }

    /**
     * Get checkedByDefault
     *
     * @return boolean $checkedByDefault
     */
    public function getCheckedByDefault()
    {
        return $this->checkedByDefault;
    }
}
