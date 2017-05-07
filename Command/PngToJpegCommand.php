<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class PngToJpegCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('png:2:jpeg')
                ->setDescription('convert png images to jpegs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->output = $output;

        $output->writeln("Start converting old images");


        $finder = new Finder();

        $files = $finder
                    ->files()
                    ->name('*.png')
                    ->in(__DIR__.'/../../../../web/uploads');

        $output->writeln("Found ".count($files)." PNGs to be converted");

        $question = new Question('How many pngs to convert?[' . count($files) . ']: ', count($files));
        $answer = $this->getHelper('question')->ask($input, $output, $question);
        $i = 0;
        foreach ($files as $file) {
            $i++;
            if($answer <= $i){ break; }
            try{
                $daPath = $file->getPathname();
                $output->writeln("Convert image ".$file->getRelativePathName().' to jpeg');
                $this->convertImage($daPath, str_replace('png','jpeg',$daPath), 85);

                $cachePath = str_replace('/web/', '/web/media/compressor/', $daPath);

                if(file_exists($cachePath)){
                    unlink($cachePath);
                    $output->writeln("Cached image removed");
                } else {
                    $output->writeln("No cached image found");
                }
                $output->writeln(" ");

            } catch (\Exception $e){
                $output->writeln("An error occured while deleting this image");
                $output->writeln($e->getMessage());
            }
        }

        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $medias = $dm->getRepository('IbtikarGlanceDashboardBundle:Media')->findBy(array('type' => 'image','deleted' => false));
        $output->writeln("Updating media");
        foreach($medias as $media){
            if(strpos($media->getPath(),'png')){
                $media->setPath(str_replace('png', 'jpeg', $media->getPath()));
                $media->setName(str_replace('png', 'jpeg', $media->getName()));
            }
        }

        $dm->flush();

        $output->writeln("Updating recipes");

        $recipes = $dm->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('defaultCoverPhoto' => array('$exists' => true),'deleted' => false));

        foreach($recipes as $recipe){
            if(strpos($recipe->getDefaultCoverPhoto(),'png')){
                $recipe->setDefaultCoverPhoto(str_replace('png', 'jpeg', $recipe->getDefaultCoverPhoto()));
            }
        }

        $dm->flush();

        $output->writeln("Command Finished");

    }

    function convertImage($originalImage, $outputImage  , $quality){
        $exploded = explode('.',$originalImage);
        $ext = $exploded[count($exploded) - 1];
        $imageTmp=imagecreatefrompng($originalImage);

        if($imageTmp){
            imagejpeg($imageTmp, $outputImage, $quality);
            imagedestroy($imageTmp);
            unlink($originalImage);
        }else {
            $output->writeln("Image failed". $imageTmp);
        }

        // quality is a value from 0 (worst) to 100 (best)
        return true;
    }

}
