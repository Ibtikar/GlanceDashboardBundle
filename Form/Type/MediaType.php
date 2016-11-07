<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;


class MediaType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('file', \Symfony\Component\Form\Extension\Core\Type\FileType::class, array('error_bubbling' => true))->addModelTransformer(new CallbackTransformer(
            function ($file) {
            // transform the array to a string
            return $file;
        }, function ($file) {
            $fileData=explode('base64,', $file);
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
                    $uploadPath = "$uploadDirectory$imageRandomName.$imageExtension";
                    $fileSystem->rename($uploadDirectory . $imageRandomName, $uploadPath);
                    $imageRandomName = "$imageRandomName.$imageExtension";
                    $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile($uploadPath, $imageRandomName, null, null, 0, true);
                    return $uploadedFile;
                }
            }
            return $file;
        }
        ));
    }

    public function getName() {
        return 'media_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

}
