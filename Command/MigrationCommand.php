<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;
use Ibtikar\GlanceDashboardBundle\Document\Tag;
use Ibtikar\GlanceDashboardBundle\Document\Media;
use Ibtikar\GlanceDashboardBundle\Document\Slug;
use Symfony\Component\HttpFoundation\File\File;

class MigrationCommand extends ContainerAwareCommand {

    private $dataDir;
    private $tags = array();
    private $dm;
    private $difficulty = array(
        'Easy' => 'easy',
        'Medium' => 'medium',
        'Difficult' => 'difficult'
    );

    protected function configure() {
        $this->dataDir = __DIR__ . "/../DataFixtures/WPData/";

        $this
                ->setName('migration:start')
                ->setDescription('Goody Kitchen data migration from json object.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $total = 897;
        $data = array();
        $helper = $this->getHelper('question');
        $question = new Question('How many records you want to migrate?[' . $total . ']: ', $total);
        $question2 = new Question('Do you want to start from spacific offset?[1]: ', 1);
        $answer = $helper->ask($input, $output, $question);
        $offset = $helper->ask($input, $output, $question2);
        $output->writeln("");
        if ($answer > 0 && $answer <= $total) {
            $progress = new ProgressBar($output, $answer);

            // start and displays the progress bar
            $progress->start();
            $progress->setOverwrite(true);

            $i = 0;

            $mediaArr = json_decode(file_get_contents($this->dataDir . 'media.json'));

            $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

            $this->getTags();

            $user = $this->dm->getRepository('IbtikarGlanceUMSBundle:Staff')->findOneByUsername('goodyAdmin');


            $z = new \XMLReader;
            $doc = new \DOMDocument;

            $z->open($this->dataDir . 'posts.xml');

            while ($z->read() && $z->name !== 'item');

            while ($z->name === 'item' && $i < $answer + $offset - 1) {

                if (++$i < $offset) {
                    $z->next('item');
                    continue;
                }

                $recipe = new Recipe();
                $recipe->setMigrated(true);
                $recipe->setPublishedBy($user);

                $media = new Media();

                $node = simplexml_import_dom($doc->importNode($z->expand(), true));

                $recipe->setTitle($node->title);

                $recipe->setPublishedAt(new \DateTime((string) $node->pubDate));

                $ns = $node->getNamespaces(true);

                $children = $node->children($ns['wp']);


                foreach ($children->postmeta as $postmeta) {
                    switch ((string) $postmeta->meta_key) {
                        case "_qts_slug_en":
                            $recipe->setTitleEn(str_replace('-', ' ', (string) $postmeta->meta_value));
                            $recipe->setSlugEn((string) $postmeta->meta_value);
                            break;
                        case "recipe-introduction-ar":
                            $recipe->setBrief(str_replace(array('[:ar]','[:en]','[:]'), '', html_entity_decode(strip_tags((string) $postmeta->meta_value))));
                            break;
                        case "recipe-introduction-en":
                            $recipe->setBriefEn(str_replace(array('[:ar]','[:en]','[:]'), '', html_entity_decode(strip_tags((string) $postmeta->meta_value))));
                            break;
                        case "recipe-ingrdients-ar":
                            $recipe->setIngredients(str_replace(array('[:ar]','[:en]','[:]'), '', (string) $postmeta->meta_value));
                            break;
                        case "recipe-ingrdients-en":
                            $recipe->setIngredientsEn(str_replace(array('[:ar]','[:en]','[:]'), '', (string) $postmeta->meta_value));
                            break;
                        case "recipe-prepare-ar":
                            $recipe->setMethod(str_replace(array('[:ar]','[:en]','[:]'), '', (string) $postmeta->meta_value));
                            break;
                        case "recipe-prepare-en":
                            $recipe->setMethodEn(str_replace(array('[:ar]','[:en]','[:]'), '', (string) $postmeta->meta_value));
                            break;
                        case "recipe-preparation-time":
                        case "recipe-total-time":
                            $date = trim((string) $postmeta->meta_value);
                            if ($date === "")
                                break;
                            preg_match('/\[\:en\](.*?)\[\:\]/', $date, $values);
                            $x = new \DateTime(empty($values)?$date:$values[1]);
                            $diff = $x->diff(new \DateTime());
                            if((string) $postmeta->meta_key == "recipe-preparation-time"){
                                $recipe->setPreparationTime($diff->i + $diff->h * 60);
                            }else{
                                $recipe->setCookingTime($diff->i + $diff->h * 60);
                            }
                            break;
                        case "recipe-servings":
                            $recipe->setServingCount((string) $postmeta->meta_value);
                            break;
                        case "recipe-difficulty":
                            $recipe->setDifficulty($this->difficulty[(string) $postmeta->meta_value]);
                            break;
                        case "_qts_slug_ar":
                            $recipe->setSlug(urldecode((string) $postmeta->meta_value));

                            break;
                        case "_thumbnail_id":
                            $id = (string) $postmeta->meta_value;
                            $data['media'] = $mediaArr->$id;
                            break;

                        default:
                            break;
                    }
                }
//            var_dump($node->xpath('//w:post_id'));
//            $recipe->getTitle($node->children("wp"));

                $data = array_merge($data, $this->CategoryHandler($node->xpath('category'), $recipe));

                $recipe->setMigrationData(serialize($data));

                $this->dm->persist($recipe);

                $progress->advance(1);

                $z->next('item');
            }

            $this->dm->flush();

            $output->writeln(PHP_EOL . $answer . " Recipes was added.");

            $this->dm->clear();
            gc_collect_cycles();

            $output->writeln(array("Start placing cover photo and publishing."));

            $this->postPersist(new ProgressBar($output,$answer));
            $progress->finish();
            $output->writeln(PHP_EOL . $answer . " Recipes was migrated and published successfully.");
        } else {
            $output->writeln(array("      (Wrong Answer)", "(ノಠ益ಠ)ノ"));
        }
    }

    private function CategoryHandler($cat, Recipe $recipe) {
        $data = array();
        foreach ($cat as $value) {

            $tag = trim((string) $value);

            if (!isset($this->tags[$tag])) {
                $NewTag = new Tag();
                $NewTag->setName($tag);
                $this->dm->persist($NewTag);
                $recipe->addTag($NewTag);
                $this->tags[$tag] = $NewTag;
            } else {
                $recipe->addTag($this->tags[$tag]);
            }

//            $recipe->addTag((string) $value);
            foreach ($value->attributes() as $key => $val) {

                if ($key == "domain") {
                    if (!isset($data[(string) $val])) {
                        $data[(string) $val] = array();
                    }

                    $data[(string) $val] = $tag;

                    switch ((string) $val) {
                        case "theme":
                            break;
                        case "products":
                            break;
                        case "recipetype":
                            break;
                        case "ingredients":
                            break;
                        case "cuisine":
                            break;
                        case "meals":
                            break;

                        default:
                            break;
                    }
                }
            }
        }

        return $data;
    }

    private function postPersist($progress) {

        // start and displays the progress bar
        $progress->start();
        $progress->setOverwrite(true);

        $recipes = $this->getContainer()->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Recipe')->findBy(array('migrated' => true));

        foreach ($recipes as $recipe) {
            $migrationData = unserialize($recipe->getMigrationData());

            $slug = new Slug();
            $slug->setReferenceId($recipe->getId());
            $slug->setType(Slug::$TYPE_RECIPE);
            $slug->setSlugAr($recipe->getSlug());
            $slug->setSlugEn($recipe->getSlugEn());
            $this->dm->persist($slug);
            $documentDir = __DIR__ . '/../../../../web/uploads/recipe-file/' . $recipe->getId() . '/';
            if (!file_exists($documentDir)) {
                mkdir($documentDir, 0755, true);
            }

            $url = $migrationData['media'];
            $imagesize = @getimagesize($url);

            $imageExt = explode('/', $imagesize['mime']);
            // $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            $extension = $imageExt[1];
            $media = new Media();
            $media->setCollectionType("recipe");
            $document = $recipe;
            $collectionSetter = "setRecipe";
            $media->$collectionSetter($document);
            $media->setOrder(0);
            $name = $media->getImagePath($extension);
            $filePath = $documentDir . $name;
            @file_put_contents($filePath, file_get_contents($migrationData['media']));
            //  $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $file = new File($filePath);
            $media->setFile($file);

            $media->setType('image');
            $media->setPath($name);
            $media->setCoverPhoto(true);
            $this->dm->persist($media);
            $recipe->setDefaultCoverPhoto($name);
            $recipe->setCoverPhoto($media);
            $publishResult = $this->getContainer()->get('recipe_operations')->publish($recipe, array());
            $progress->advance();
        }

        $this->dm->flush();
        $progress->finish();
    }

    private function getTags() {
        $tags = $this->getContainer()->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Tag')->findAll();
        foreach ($tags as $tag) {
            $this->tags[$tag->getName()] = $tag;
        }
    }

}
