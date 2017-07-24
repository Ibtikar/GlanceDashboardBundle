<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceUMSBundle\Validator\Constraints\ImageValid;
use Ibtikar\GlanceUMSBundle\Validator\Constraints\ImageExtensionValid;

/**
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 *
 */
class Sponsor {

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $name;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $nameEn;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $price;

    /**
     * @Assert\NotBlank
     * @MongoDB\String
     */
    protected $website;

    /**
     * @MongoDB\String
     */
    protected $image;

    /**
     * a temp variable for storing the old image name to delete the old image after the update
     */
    private $temp;

    /**
     * @Assert\Image(maxSize="2097152", maxSizeMessage="Image size must be less than 2mb", minWidth=200, maxWidth = 200, minHeight=200,maxHeight = 200, minWidthMessage="Image dimension must than 819*1091", minHeightMessage="Image dimension must than 200*200", minWidthMessage="Image dimension must than 200*200", minHeightMessage="Image dimension must than 200*200", mimeTypes={"image/jpeg", "image/pjpeg", "image/png", "image/gif"}, mimeTypesMessage="picture extension not correct.")
     * @ImageValid
     * @ImageExtensionValid
     * @Assert\NotBlank
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $file;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return self
     */
    public function setWebsite($website) {
        $this->website = $website;
        return $this;
    }

    /**
     * Get website
     *
     * @return string $website
     */
    public function getWebsite() {
        return $this->website;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return User
     */
    public function setImage($image) {
        $this->image = $image;
        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Set file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return User
     */
    public function setFile($file) {
        $this->file = $file;
        //check if we have an old image
        if ($this->image) {
            //store the old name to delete on the update
            $this->temp = $this->image;
            $this->image = NULL;
        } else {
            $this->image = 'initial';
        }
        return $this;
    }

    /**
     * Get file
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * this function is used to delete the current image
     * the deleting of the current object will also delete the image and you do not need to call this function
     * if you call this function before you remove the object the image will not be removed
     */
    public function removeImage() {
        //check if we have an old image
        if ($this->image) {
            //store the old name to delete on the update
            $this->temp = $this->image;
            //delete the current image
            $this->image = NULL;
        }
    }

    /**
     * create the the directory if not found
     * @param string $directoryPath
     * @throws \Exception if the directory can not be created
     */
    private function createDirectory($directoryPath) {
        if (!@is_dir($directoryPath)) {
            $oldumask = umask(0);
            $success = @mkdir($directoryPath, 0755, TRUE);
            umask($oldumask);
            if (!$success) {
                throw new \Exception("Can not create the directory $directoryPath");
            }
        }
    }

    /**
     * @MongoDB\PrePersist()
     * @MongoDB\PreUpdate()
     */
    public function preUpload() {
        if (NULL !== $this->file && (NULL === $this->image || 'initial' === $this->image)) {
            //get the image extension
            $extension = $this->file->guessExtension();
            //generate a random image name
            $img = uniqid();
            //get the image upload directory
            $uploadDir = $this->getUploadRootDir();
            $this->createDirectory($uploadDir);
            //check that the file name does not exist
            while (@file_exists("$uploadDir/$img.$extension")) {
                //try to find a new unique name
                $img = uniqid();
            }
            //set the image new name
            $this->image = "$img.$extension";
        }
    }

    /**
     * @MongoDB\PostPersist()
     * @MongoDB\PostUpdate()
     */
    public function upload() {
        if (NULL !== $this->file) {
            // you must throw an exception here if the file cannot be moved
            // so that the document is not persisted to the database
            // which the UploadedFile move() method does

            if ($this->file->guessExtension() == $this->file->getExtension() && $this->file->getExtension() == "png") {
                $this->convertImage($this->file->getPathname(), $this->getUploadRootDir() . "/" . str_replace('png', 'jpg', $this->path), 85);
                $this->setPath(str_replace('png', 'jpg', $this->path));
            } else {
                if ($this->file->guessExtension() == 'png' && in_array($this->file->getExtension(), array('jpg', "jpeg"))) {
                    $imageTmp = imagecreatefrompng($this->file->getPathname());
                    imagejpeg($imageTmp, $this->getUploadRootDir() . "/" .  $this->path, 85);
                    imagedestroy($imageTmp);
                } else {
                    $this->file->move($this->getUploadRootDir(), $this->path);
                }
            }

            // remove the file as you do not need it any more
            $this->file = NULL;
        }
        // check if we have an old file
        if ($this->temp) {
            // try to delete the old file
            @unlink($this->getUploadRootDir() . '/' . $this->temp);
            // clear the temp file
            $this->temp = NULL;
        }
    }

    /**
     * @MongoDB\PostRemove()
     */
    public function postRemove() {
        //check if we have an image
        if ($this->image) {
            //try to delete the image
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of image starting of root
     */
    public function getAbsolutePath() {
        return $this->getUploadRootDir() . '/' . $this->image;
    }

    /**
     * @return string the relative path of image starting from web directory
     */
    public function getWebPath() {
        $image = $this->image;
        return NULL === $image ? NULL : $this->getUploadDir() . '/' . $image;
    }

    /**
     * @return string the path of upload directory starting of root
     */
    public function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    protected function getUploadDir() {
        return 'uploads/sponsor';
    }

    function convertImage($originalImage, $outputImage, $quality){
        $exploded = explode('.',$originalImage);
        $ext = $exploded[count($exploded) - 1];
        if (preg_match('/png/i',$ext)){$imageTmp=imagecreatefrompng($originalImage);}
        else    {    return false;}
        // quality is a value from 0 (worst) to 100 (best)
        imagejpeg($imageTmp, $outputImage, $quality);
        imagedestroy($imageTmp);
        unlink($originalImage);
        return true;
    }


    /**
     * Set nameEn
     *
     * @param string $nameEn
     * @return self
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
        return $this;
    }

    /**
     * Get nameEn
     *
     * @return string $nameEn
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return string $price
     */
    public function getPrice()
    {
        return $this->price;
    }
}
