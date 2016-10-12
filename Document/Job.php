<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Ibtikar\GlanceDashboardBundle\Document\Document;


/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\JobRepository")
 * @MongoDBUnique(fields={"title"})
 * @MongoDBUnique(fields={"title_en"})
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"title"="asc"}),
 *   @MongoDB\Index(keys={"title_en"="asc"})
 * })
 */
class Job extends Document {


    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $title;

    /**
     * @MongoDB\String
     */
    private $titleSingle;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     * @Assert\Length(
     *      max = 150,
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters long"
     * )
     */
    private $title_en;

    /**
     * @MongoDB\Increment
     */
    private $staffMembersCount = 0;

    /**
     * @MongoDB\Int
     */
    private $staffMembersMaxCount;

    public function __toString() {
        return (string) $this->title;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set titleSingle
     *
     * @param string $titleSingle
     * @return self
     */
    public function setTitleSingle($titleSingle) {
        $this->titleSingle = $titleSingle;
        return $this;
    }

    /**
     * Get titleSingle
     *
     * @return string $titleSingle
     */
    public function getTitleSingle() {
        return $this->titleSingle;
    }

    /**
     * Set titleEn
     *
     * @param string $titleEn
     * @return self
     */
    public function setTitleEn($titleEn) {
        $this->title_en = $titleEn;
        return $this;
    }

    /**
     * Get titleEn
     *
     * @return string $titleEn
     */
    public function getTitleEn() {
        return $this->title_en;
    }

    /**
     * Get staffCount
     *
     * @return int $staffCount
     */
    public function getStaffCount($dm) {
        $staffMembers = $dm->createQueryBuilder('IbtikarGlanceUMSBundle:Staff')
                        ->field('job')->equals($this->getId())
                        ->field('deleted')->equals(FALSE)->count()
                        ->getQuery()->execute();

        return $staffMembers;
    }

    /**
     * Set staffMembersCount
     *
     * @param increment $staffMembersCount
     * @return self
     */
    public function setStaffMembersCount($staffMembersCount) {
        $this->staffMembersCount = $staffMembersCount;
        return $this;
    }

    /**
     * Get staffMembersCount
     *
     * @return increment $staffMembersCount
     */
    public function getStaffMembersCount() {
        return $this->staffMembersCount;
    }

    /**
     * Set staffMembersMaxCount
     *
     * @param increment $staffMembersMaxCount
     * @return self
     */
    public function setStaffMembersMaxCount($staffMembersMaxCount) {
        $this->staffMembersMaxCount = $staffMembersMaxCount;
        return $this;
    }

    /**
     * Get staffMembersMaxCount
     *
     * @return increment $staffMembersMaxCount
     */
    public function getStaffMembersMaxCount() {
        return $this->staffMembersMaxCount;
    }


}
