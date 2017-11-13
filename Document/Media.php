<?php

namespace Ibtikar\GlanceDashboardBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ibtikar\GlanceUMSBundle\Validator\Constraints\ImageExtensionValid;
use Ibtikar\GlanceUMSBundle\Validator\Constraints\FileExtensionValid;
use Ibtikar\GlanceUMSBundle\Validator\Constraints\ImageValid;
use Ibtikar\GlanceDashboardBundle\Document\Document;

/**
 * @MongoDB\Document(repositoryClass="Ibtikar\GlanceDashboardBundle\Document\MediaRepository")
 * @MongoDB\HasLifecycleCallbacks
 * @MongoDB\Indexes({
 * })
 * @IgnoreAnnotation("KeepReference")
 */
class Media extends Document {

    /**
     * @MongoDB\Id
     */
    private $id;



    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Product", simple=true)
     * @KeepReference
     */
    private $product;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\HomeBanner", simple=true)
     * @KeepReference
     */
    private $banner;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\SubProduct", simple=true)
     * @KeepReference
     */
    private $subproduct;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\SubProduct", simple=true)
     * @KeepReference
     */
    private $activity;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\CausePage", simple=true)
     * @KeepReference
     */
    private $causePage;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Recipe", simple=true)
     * @KeepReference
     */
    private $recipe;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Magazine", simple=true)
     * @KeepReference
     */
    private $magazine;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Competition", simple=true)
     * @KeepReference
     */
    private $competition;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GoodyFrontendBundle\Document\ContactMessage", simple=true)
     * @KeepReference
     */
    private $contactMessage;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Ibtikar\GlanceDashboardBundle\Document\Season", simple=true)
     * @KeepReference
     */
    private $season;

    /**
     * @MongoDB\String
     */
    private $captionAr;

    /**
     * @MongoDB\String
     */
    private $captionEn;

    /**
     * @MongoDB\String
     */
    private $fileType;

    /**
     * @MongoDB\String
     */
    private $name;

    /**
     * @MongoDB\String
     */
    private $path;

    /**
     * @MongoDB\String
     */
    private $tempPath;

    /**
     * @MongoDB\Int
     */
    private $order;



    /**
     * @MongoDB\String
     */
    private $type;

    /**
     * @MongoDB\String
     */
    private $collectionType = 'Product';

    /**
     * a temp variable for storing the old name to delete the old file after the update
     */
    private $temp;

    /**
     * @MongoDB\Boolean
     */
    private $coverPhoto = false;

    /**
     * @MongoDB\String
     */
    private $descriptionAr;

    /**
     * @MongoDB\String
     */
    private $descriptionEn;

    /**
     * @MongoDB\Boolean
     */
    private $ProfilePhoto = false;

    /**
     * @MongoDB\Boolean
     */
    private $naturalPhoto = false;

    /**
     * @MongoDB\Boolean
     */
    private $bannerPhoto = false;

    /**
     * @MongoDB\Boolean
     */
    private $activityPhoto = false;

    /**
     * @MongoDB\String
     */
    private $vid;

    /**
     * @MongoDB\String
     */
    private $extraInfo;

    /**
     * @MongoDB\String
     */
    private $uniqueId;

    /**
     * @Assert\Image(maxSize="4194304", maxSizeMessage="Image size must be less than 4mb", minWidth=819, maxWidth = 819, minHeight=1091,maxHeight = 1091, minWidthMessage="Image dimension must than 819*1091", minHeightMessage="Image dimension must than 819*1091", minWidthMessage="Image dimension must than 819*1091", minHeightMessage="Image dimension must than 819*1091", mimeTypes={"image/jpeg", "image/pjpeg", "image/png", "image/gif"}, mimeTypesMessage="picture extension not correct.", groups={"Magazine"})
     * @Assert\Image(maxSize="2097152", maxSizeMessage="Image size must be less than 2mb", minWidth=200,  minHeight=200, minWidthMessage="Image dimension must than 200*200", minHeightMessage="Image dimension must than 200*200", mimeTypes={"image/jpeg", "image/pjpeg", "image/png", "image/gif"}, mimeTypesMessage="picture extension not correct.", groups={"contactMessage"})
     * @Assert\File(maxSize="4194304", maxSizeMessage="File size must be less than 4mb", mimeTypes={"application/pdf", "application/vnd.ms-office", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/excel", "application/x-excel", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/msword", "application/zip", "application/x-zip", "application/x-zip-compressed"}, mimeTypesMessage="file not correct.", groups={"file"})
     * @Assert\Image(maxSize="4194304", maxSizeMessage="Image size must be less than 4mb", minWidth=1170, minHeight=600, minWidthMessage="Image dimension must be more than 1170*600", minHeightMessage="Image dimension must be more than 1170*600", mimeTypes={"image/jpeg", "image/pjpeg", "image/png","image/gif"}, mimeTypesMessage="picture extension not correct.", groups={"product"})
     * @Assert\Image(maxSize="4194304", maxSizeMessage="Image size must be less than 4mb", minWidth=1170, minHeight=600, minWidthMessage="Image dimension must be more than 1170*600", minHeightMessage="Image dimension must be more than 1170*600", mimeTypes={"image/jpeg", "image/pjpeg", "image/png","image/gif"}, mimeTypesMessage="picture extension not correct.", groups={"recipe","blog"})
     * @ImageValid(groups={"image","contactMessageImage"})
     * @ImageExtensionValid(groups={"image", "contactMessageImage"})
     * @FileExtensionValid(extensions={"doc", "docx", "xls", "xlsx", "pdf", "zip"}, groups={"file","contactMessageFile"})
     * @Assert\NotBlank(groups={"image", "file"})
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $file;

    /**
     * Set file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return User
     */
    public function setFile($file) {
        $this->file = $file;
        //check if we have an old image
        if ($this->path) {
            //store the old name to delete on the update
            $this->temp = $this->path;
            $this->path = NULL;
        } else {
            $this->path = 'initial';
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
     * this function is used to delete the current file
     * the deleting of the current object will also delete the file and you do not need to call this function
     * if you call this function before you remove the object the file will not be removed
     */
    public function removeFile() {
        //check if we have an old image
        if ($this->path) {
            //store the old name to delete on the update
            $this->temp = $this->path;
            //delete the current image
            $this->path = NULL;
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
        if ($this->file instanceof UploadedFile && (NULL === $this->path || 'initial' === $this->path)) {
            // get the file extension
            $extension = strtolower($this->file->getClientOriginalExtension());
            // generate a random file name
            $img = uniqid();
            // get the upload directory
            $uploadDir = $this->getUploadRootDir();
            $this->createDirectory($uploadDir);
            // check that the file name does not exist
            while (@file_exists("$uploadDir/$img.$extension")) {
                //try to find a new unique name
                $img = uniqid();
            }
            // set the new name
            $this->path = "$img.$extension";
            if (in_array($extension, array('xls', 'xlsx'))) {
                $this->fileType = 'excel';
            }
            if (in_array($extension, array('doc', 'docx'))) {
                $this->fileType = 'word';
            }
            if ($extension === 'pdf') {
                $this->fileType = 'pdf';
            }
            if ($extension === 'zip') {
                $this->fileType = 'zip';
            }
            $this->name = $this->file->getClientOriginalName();
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
        // check if we have a file
        if ($this->path) {
            // try to delete the file
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of file starting of root
     */
    public function getAbsolutePath() {
        return $this->getUploadRootDir() . '/' . $this->path;
    }

    /**
     * @return string the relative path of file starting from web directory
     */
    public function getWebPath() {
        return NULL === $this->path ? NULL : $this->getUploadDir() . '/' . $this->path;
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
    protected function getUploadDir()
    {
        $uploadDirectory = 'uploads';
        if ($this->getProduct()) {
            $uploadDirectory .= '/product-file/' . $this->getProduct()->getId();
        } else if ($this->getSubproduct()) {
            $uploadDirectory .= '/subproduct-file/' . $this->getSubproduct()->getId();

        } else if ($this->getActivity()) {
            $uploadDirectory .= '/activity-file/' . $this->getActivity()->getId();
        } else if ($this->getRecipe()) {
            $uploadDirectory .= "/" . strtolower($this->collectionType) . "-file/" . $this->getRecipe()->getId();
        } else if ($this->getContactMessage()) {
            $uploadDirectory .= "/".strtolower($this->collectionType)."-file/" . $this->getContactMessage()->getId();
        }
        else if ($this->getMagazine()) {
            $uploadDirectory .= '/magazine-file/' . $this->getMagazine()->getId();
        } else if ($this->getUniqueId()) {
            $uploadDirectory .= '/competition-file/' . $this->getUniqueId();
        } else if ($this->getCompetition()) {
            $uploadDirectory .= '/competition-file/' . $this->getCompetition()->getId();
        } else {
            if (!$this->getCreatedBy()) {
                throw new \Exception('Please set the created by user.');
            }
            $uploadDirectory .= '/' . strtolower($this->getCollectionType()) . '-file/' . $this->getCreatedBy()->getId();
        }
        return $uploadDirectory;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

//    /**
//     * @param ExecutionContextInterface $context
//     */
//    public function isTypeValid(ExecutionContextInterface $context) {
//        if (!in_array($this->getType(), $this->getValidTypes())) {
//            $context->addViolationAt('type', 'This value is not correct.');
//        }
//    }

    /**
     * @return array
     */
    public static function getValidTypes() {
        return array(
            'file' => 'file',
            'image' => 'image'
        );
    }



    public function getImagePath($extension) {
        // generate a random file name
        $img = uniqid();
        // get the upload directory
        $uploadDir = $this->getUploadRootDir();
        $this->createDirectory($uploadDir);
        // check that the file name does not exist
        while (@file_exists("$uploadDir/$img.$extension")) {
            //try to find a new unique name
            $img = uniqid();
        }
        // set the new name
        return "$img.$extension";
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return string
     */
    public function getDownloadName() {
        $fileName = $this->name;
        $fileNameParts = explode('.', $fileName);
        $fileNameExtension = array_pop($fileNameParts);
        $caption = $this->getCaption();
        if ($caption) {
            $fileName = $caption;
            $captionParts = explode('.', $caption);
            $captionExtension = array_pop($captionParts);
            if ($captionExtension !== $fileNameExtension) {
                $fileName .= '.' . $fileNameExtension;
            }
        }
        return $fileName;
    }


    /**
     * @return string
     */
    public function getFileIcon() {
        if ($this->fileType === 'pdf') {
            return 'pdf.jpg';
        }
        if ($this->fileType === 'excel') {
            return 'ex.jpg';
        }
        if ($this->fileType === 'word') {
            return 'wor.jpg';
        }
        if ($this->fileType === 'zip') {
            return 'rar.jpg';
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getFileIconPath() {
        return $this->getFileIcon() ? '/design/website/images/' . $this->getFileIcon() : null;
    }

    public function getIconOfFiles() {
        if ($this->fileType === 'pdf') {
            return 'flaticon-interface-1';
        }
        if ($this->fileType === 'excel') {
            return 'flaticon-excel-file';
        }
        if ($this->fileType === 'word') {
            return 'flaticon-word';
        }
        if ($this->fileType === 'zip') {
            return 'flaticon-zip-file';
        }
        return null;
    }


    /**
     * Set product
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Product $product
     * @return self
     */
    public function setProduct(\Ibtikar\GlanceDashboardBundle\Document\Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Product $product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set caption
     *
     * @param string $caption
     * @return self
     */
    public function setCaptionAr($caption)
    {
        $this->captionAr = $caption;
        return $this;
    }

    /**
     * Get caption
     *
     * @return string $caption
     */
    public function getCaptionAr()
    {
        return $this->captionAr;
    }

    /**
     * Set captionEn
     *
     * @param string $captionEn
     * @return self
     */
    public function setCaptionEn($captionEn)
    {
        $this->captionEn = $captionEn;
        return $this;
    }

    /**
     * Get captionEn
     *
     * @return string $captionEn
     */
    public function getCaptionEn()
    {
        return $this->captionEn;
    }

    /**
     * Set fileType
     *
     * @param string $fileType
     * @return self
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
        return $this;
    }

    /**
     * Get fileType
     *
     * @return string $fileType
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return self
     */
    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
        return $this;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getTempPath()
    {
        return $this->tempPath;
    }
    /**
     * Set path
     *
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set order
     *
     * @param int $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set collectionType
     *
     * @param string $collectionType
     * @return self
     */
    public function setCollectionType($collectionType)
    {
        $this->collectionType = $collectionType;
        return $this;
    }

    /**
     * Get collectionType
     *
     * @return string $collectionType
     */
    public function getCollectionType()
    {
        return $this->collectionType;
    }

    /**
     * Set coverPhoto
     *
     * @param boolean $coverPhoto
     * @return self
     */
    public function setCoverPhoto($coverPhoto)
    {
        $this->coverPhoto = $coverPhoto;
        return $this;
    }

    /**
     * Get coverPhoto
     *
     * @return boolean $coverPhoto
     */
    public function getCoverPhoto()
    {
        return $this->coverPhoto;
    }

    /**
     * Set profilePhoto
     *
     * @param boolean $profilePhoto
     * @return self
     */
    public function setProfilePhoto($profilePhoto)
    {
        $this->ProfilePhoto = $profilePhoto;
        return $this;
    }

    /**
     * Get profilePhoto
     *
     * @return boolean $profilePhoto
     */
    public function getProfilePhoto()
    {
        return $this->ProfilePhoto;
    }

    /**
     * Set subproduct
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\SubProduct $subproduct
     * @return self
     */
    public function setSubproduct(\Ibtikar\GlanceDashboardBundle\Document\SubProduct $subproduct)
    {
        $this->subproduct = $subproduct;
        return $this;
    }

    /**
     * Get subproduct
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\SubProduct $subproduct
     */
    public function getSubproduct()
    {
        return $this->subproduct;
    }

    public function __toString() {
        if($this->type == "video"){
            return $this->getVid();
        }else{
            return $this->getWebPath();
        }
    }

    /**
     * Set recipe
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Recipe $recipe
     * @return self
     */
    public function setRecipe(\Ibtikar\GlanceDashboardBundle\Document\Recipe $recipe)
    {
        $this->recipe = $recipe;
        return $this;
    }

    /**
     * Get recipe
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Recipe $recipe
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set vid
     *
     * @param string $vid
     * @return self
     */
    public function setVid($vid)
    {
        $this->vid = $vid;
        return $this;
    }

    /**
     * Get vid
     *
     * @return string $vid
     */
    public function getVid()
    {
        return $this->vid;
    }

    /**
     * Set magazine
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Magazine $magazine
     * @return self
     */
    public function setMagazine(\Ibtikar\GlanceDashboardBundle\Document\Magazine $magazine)
    {
        $this->magazine = $magazine;
        return $this;
    }

    /**
     * Get magazine
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Magazine $magazine
     */
    public function getMagazine()
    {
        return $this->magazine;
    }

    /**
     * Set contactMessage
     *
     * @param Ibtikar\GoodyFrontendBundle\Document\ContactMessage $contactMessage
     * @return self
     */
    public function setContactMessage(\Ibtikar\GoodyFrontendBundle\Document\ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
        return $this;
    }

    /**
     * Get contactMessage
     *
     * @return Ibtikar\GoodyFrontendBundle\Document\ContactMessage $contactMessage
     */
    public function getContactMessage()
    {
        return $this->contactMessage;
    }

    /**
     * Set competition
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Competition $competition
     * @return self
     */
    public function setCompetition(\Ibtikar\GlanceDashboardBundle\Document\Competition $competition)
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * Get competition
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Competition $competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Set extraInfo
     *
     * @param string $extraInfo
     * @return self
     */
    public function setExtraInfo($extraInfo)
    {
        $this->extraInfo = $extraInfo;
        return $this;
    }

    /**
     * Get extraInfo
     *
     * @return string $extraInfo
     */
    public function getExtraInfo()
    {
        return $this->extraInfo;
    }

    /**
     * Set uniqueId
     *
     * @param string $uniqueId
     * @return self
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * Get uniqueId
     *
     * @return string $uniqueId
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Set bannerPhoto
     *
     * @param boolean $bannerPhoto
     * @return self
     */
    public function setBannerPhoto($bannerPhoto)
    {
        $this->bannerPhoto = $bannerPhoto;
        return $this;
    }

    /**
     * Get bannerPhoto
     *
     * @return boolean $bannerPhoto
     */
    public function getBannerPhoto()
    {
        return $this->bannerPhoto;
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
     * Set banner
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\HomeBanner $banner
     * @return self
     */
    public function setBanner(\Ibtikar\GlanceDashboardBundle\Document\HomeBanner $banner)
    {
        $this->banner = $banner;
        return $this;
    }

    /**
     * Get banner
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\HomeBanner $banner
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * Set activityPhoto
     *
     * @param boolean $activityPhoto
     * @return self
     */
    public function setActivityPhoto($activityPhoto)
    {
        $this->activityPhoto = $activityPhoto;
        return $this;
    }

    /**
     * Get activityPhoto
     *
     * @return boolean $activityPhoto
     */
    public function getActivityPhoto()
    {
        return $this->activityPhoto;
    }


    /**
     * Set activity
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\SubProduct $activity
     * @return self
     */
    public function setActivity(\Ibtikar\GlanceDashboardBundle\Document\SubProduct $activity)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * Get activity
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\SubProduct $activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set naturalPhoto
     *
     * @param boolean $naturalPhoto
     * @return self
     */
    public function setNaturalPhoto($naturalPhoto)
    {
        $this->naturalPhoto = $naturalPhoto;
        return $this;
    }

    /**
     * Get naturalPhoto
     *
     * @return boolean $naturalPhoto
     */
    public function getNaturalPhoto()
    {
        return $this->naturalPhoto;
    }

    /**
     * Set causePage
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\CausePage $causePage
     * @return self
     */
    public function setCausePage(\Ibtikar\GlanceDashboardBundle\Document\CausePage $causePage)
    {
        $this->causePage = $causePage;
        return $this;
    }

    /**
     * Get causePage
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\CausePage $causePage
     */
    public function getCausePage()
    {
        return $this->causePage;
    }

    /**
     * Set season
     *
     * @param Ibtikar\GlanceDashboardBundle\Document\Season $season
     * @return self
     */
    public function setSeason(\Ibtikar\GlanceDashboardBundle\Document\Season $season)
    {
        $this->season = $season;
        return $this;
    }

    /**
     * Get season
     *
     * @return Ibtikar\GlanceDashboardBundle\Document\Season $season
     */
    public function getSeason()
    {
        return $this->season;
    }


    /**
     * Set descriptionEn
     *
     * @param string $descriptionEn
     * @return self
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;
        return $this;
    }

    /**
     * Get descriptionEn
     *
     * @return string $descriptionEn
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * Set descriptionAr
     *
     * @param string $descriptionAr
     * @return self
     */
    public function setDescriptionAr($descriptionAr)
    {
        $this->descriptionAr = $descriptionAr;
        return $this;
    }

    /**
     * Get descriptionAr
     *
     * @return string $descriptionAr
     */
    public function getDescriptionAr()
    {
        return $this->descriptionAr;
    }
}
