<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ibtikar\GlanceDashboardBundle\Document\Recipe;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class RecipeType extends AbstractType {

    private $config = array();

    private $defaultConfig = array(
        "tagSelected" => null
    );

    /**
     * @param boolean|array|string $validationGroups
     */
    public function __construct($config = array(), $tagSelected = null) {
        $this->config = array_merge($this->defaultConfig, $config);
        $this->tagSelected = $tagSelected;
    }



    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('title', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('titleEn', formType\TextType::class, array('label' => 'title','attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('hideEnglishContent', formType\CheckboxType::class, array('required' => false,'attr'=>array('class'=>'styled')))
                ->add('brief',  formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('briefEn',formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('tags', formType\TextType::class, array('mapped' => false, 'required' => FALSE, 'attr' => array('data-tag-input'=>true,'data-rule-taglength' => 330),'label_attr' => array()))
                ->add('tagsEn', formType\TextType::class, array('mapped' => false, 'required' => FALSE, 'attr' => array('data-tag-input'=>null,'data-rule-taglength' => 330),'label_attr' => array()))
                ;
                
                
                if($options['attr']['contentType'] == 'blog') {
                    $blogTypes = Recipe::$types;
                    array_shift($blogTypes);
                    $builder->add('text',  CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                            ->add('textEn',CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')));
                    
                        if($options['attr']['type']=='add'){
                            $builder->add('type', formType\ChoiceType::class, 
                                    array(
                                        'required' => TRUE, 
                                        'choices' => $blogTypes,
                                        'expanded' => true, 
                                        'placeholder' => false, 
                                        'data' => $blogTypes['article'],
                                        'choice_translation_domain'=>'recipe'
                                    )
                            );
                        }
                }
        
                if($options['attr']['contentType'] == 'recipe') {
                    $builder->add('ingredients',  CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                        ->add('ingredientsEn',CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                        ->add('method',  CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                        ->add('methodEn',CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                ;           
                }
                            
                $builder->add('chef', DocumentType::class, array('required' => false,'placeholder' => 'اختار الشيف','class' => 'IbtikarGlanceUMSBundle:Staff', 'attr' => array('data-img-method'=>'webPath','data-img-default'=>'bundles/ibtikarshareeconomydashboarddesign/images/profile.jpg','class' => 'select-with-thumb')));
                
                if($options['attr']['contentType'] == 'recipe') {
                    $builder->add('country', DocumentType::class, array('required' => false,'class' => 'IbtikarGlanceUMSBundle:Country', 'query_builder' => function($repo) {
                        return $repo->findCountrySorted();
                    },'placeholder' => 'اختار البلد', 'choice_label' => 'countryName', 'attr' => array('data-country' => true, 'class' => 'dev-country select')))                ->add('preparationTime', formType\TextType::class, array('required' => false, 'attr' => array()))
                    ->add('cookingTime', formType\TextType::class, array('required' => false, 'attr' => array('data-rule-number'=>true)))
                    ->add('servingCount', formType\TextType::class, array('required' => false, 'attr' => array('data-rule-number'=>true)))
                    ->add('difficulty', formType\ChoiceType::class, array('required' => FALSE,
                        'choices' => Recipe::$difficultyMap,
                        'expanded' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe'
                    ))
                    ->add('course', formType\ChoiceType::class, array('required' => FALSE,
                        'choices' => array_flip(Recipe::$courseMap),
                        'multiple' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe','attr' => array('data-maximum-selection-length'=> 3,'class' => 'select-multiple')
                    ))
                    ->add('meal', formType\ChoiceType::class, array('required' => FALSE,
                        'choices' => array_flip(Recipe::$mealMap),
                        'multiple' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe','attr' => array('data-maximum-selection-length'=> 3,'class' => 'select-multiple')
                    ));
    //                if($options['type']=='edit'){
    //                $builder->add('tagSelected', formType\HiddenType::class, array('mapped' => false, 'required' => FALSE, 'data' => $options['tagSelected']))
    //                ->add('tagEnSelected', formType\HiddenType::class, array('mapped' => false, 'required' => FALSE, 'data' =>$options['tagEnSelected'] ));
    //                }
                    
                    
                    $builder->add('keyIngredient', formType\ChoiceType::class, array('required' => FALSE,
                        'choices' => array_flip(Recipe::$keyIngredientMap),
                        'multiple' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe','attr' => array('data-maximum-selection-length'=> 3,'class' => 'select-multiple')
                    ));
                }
                
                        
                $builder->add('products', DocumentType::class, array('required' => false,'multiple' => 'multiple','placeholder' => 'Choose Product','class' => 'IbtikarGlanceDashboardBundle:Product', 'attr' => array('data-maximum-selection-length'=> 10,'data-img-method'=>'profilePhoto','data-img-default'=>'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg','class' => 'select-with-thumb')));
//                ->add('relatedMaterialId', formType\TextType::class, array('attr' => array("data-validation-message"=>'','data-rule-unique' => 'material_check_field_unique', 'data-name' => 'relatedMaterial'), 'required' => FALSE, 'mapped' => false, 'data' => $this->relatedMaterialId))
//                ->add('relatedRecipe', formType\TextType::class, array(
//                    'mapped' => false,
//                    'data' => $builder->getData()->getRelatedMaterialsJson(),
//                    'required' => false,
//                    'attr'=>array(
//                        'class'=> 'hide'
//                    )
//                ))
                        
                if($options['attr']['contentType'] == 'recipe') {
                    $builder->add('relatedRecipe', formType\ChoiceType::class, array('multiple'=>true,'required' => FALSE,'mapped' => FALSE,
                        'choice_translation_domain'=>'recipe','attr' => array('class' => 'select-ajax', 'data_related_container'=>'recipe_related', 'ajax-url-var' => 'relatedMaterialSearchUrl')
                    ))
                    ->add('related', formType\TextareaType::class, array('required' => FALSE, "mapped" => false,'attr'=>array('parent-class'=>'hidden')))
                    ;
                } else {
                    $builder->add('relatedArticle', formType\ChoiceType::class, array('multiple'=>true,'required' => FALSE,'mapped' => FALSE,
                        'choice_translation_domain'=>'recipe','attr' => array('class' => 'select-ajax', 'data_related_container'=>'recipe_related_article', 'ajax-url-var' => 'relatedArticleSearchUrl')))
                    ->add('relatedTip', formType\ChoiceType::class, array('multiple'=>true,'required' => FALSE,'mapped' => FALSE,
                        'choice_translation_domain'=>'recipe','attr' => array('class' => 'select-ajax', 'data_related_container'=>'recipe_related_tip', 'ajax-url-var' => 'relatedTipSearchUrl')
                    ))
                    ->add('related_article', formType\TextareaType::class, array('required' => FALSE, "mapped" => false,'attr'=>array('parent-class'=>'hidden')))
                    ->add('related_tip', formType\TextareaType::class, array('required' => FALSE, "mapped" => false,'attr'=>array('parent-class'=>'hidden')))
                    ;
                }
                        
                        
                $builder->add('defaultCoverPhoto', formType\HiddenType::class,array("mapped" => false,'required' => true,'attr'=>array('data-msg-required'=>' ')))
                ->add('galleryType', formType\HiddenType::class)
                ->add('media', formType\TextareaType::class, array('required' => FALSE, "mapped" => false,'attr'=>array('parent-class'=>'hidden')))
                ->add('submitButton', formType\SubmitType::class);
    }


    /**
     * @return string
     */
    public function getName() {
        return 'recipe_type';
    }

    public function configureOptions( \Symfony\Component\OptionsResolver\OptionsResolver $resolver ) {
    $resolver->setDefaults( [
      'type' => 'create',
      'tagSelected' => '',
      'tagEnSelected' => '',

        ]);
    }

}
