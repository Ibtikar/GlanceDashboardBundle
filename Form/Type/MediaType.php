<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;



class MediaType extends AbstractType
{

    private $extension;

     public function __construct(array $options = array())
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }



    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->extension=$options['extension'];
        $builder->add('file', \Symfony\Component\Form\Extension\Core\Type\FileType::class, array('error_bubbling' => true))
        ->addModelTransformer(new CallbackTransformer(
            function ($file) {
            // transform the array to a string
            return $file;
        }, function ($media) {
            $fileData=$media->getFile();

            $fileData=explode('base64,', $fileData);
            $imageString = base64_decode($fileData[1]);
            $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
            if ($imageString) {

                $imageRandomName = uniqid();
                $uploadDirectory = $media->getUploadRootDir() . '/temp/';
                $fileSystem->mkdir($uploadDirectory, 0755);
                $uploadPath = $uploadDirectory . $imageRandomName;

                if (@file_put_contents($uploadPath, $imageString)) {
                    $file = new \Symfony\Component\HttpFoundation\File\File($uploadPath, false);
                    $imageExtension = $file->guessExtension();
                    if ($this->extension) {
                        $imageExtension = $this->extension;
                    }
                    $uploadPath = "$uploadDirectory$imageRandomName.$imageExtension";
                    $fileSystem->rename($uploadDirectory . $imageRandomName, $uploadPath);
                    $imageRandomName = "$imageRandomName.$imageExtension";
                    $tempUrlPath = $uploadPath;

                    $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile($uploadPath, $imageRandomName, null, null, 0, true);
                    $media->setFile($uploadedFile);
                    $media->setTempPath($tempUrlPath);

                    return $media;
                }
            }
            return $media;
        }
        ));

    }

    public function getName() {
        return 'media_type';
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'extension' => ''
        ]);
    }
}
