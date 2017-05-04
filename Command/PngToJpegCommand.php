<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;

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
        $i = 0;
        foreach ($files as $file) {
            try{
                $daPath = $file->getPathname();
                $output->writeln("Convert image ".$file->getRelativePathName().' jpeg');
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

        $output->writeln("Command Finished");

    }

    function convertImage($originalImage, $outputImage, $quality){
        $exploded = explode('.',$originalImage);
        $ext = $exploded[count($exploded) - 1];
        $imageTmp=imagecreatefrompng($originalImage);
        // quality is a value from 0 (worst) to 100 (best)
        imagejpeg($imageTmp, $outputImage, $quality);
        imagedestroy($imageTmp);
        unlink($originalImage);
        return true;
    }

}
