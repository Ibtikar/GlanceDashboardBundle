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
    public function __construct($config = array()) {
        $this->config = array_merge($this->defaultConfig, $config);
    }
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('title', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('titleEn', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('brief',  formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('briefEn',formType\TextareaType::class, array('required' => FALSE,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 150,'data-rule-minlength' => 3)))
                ->add('tags', formType\TextType::class, array('mapped' => false, 'required' => FALSE, 'attr' => array('data-tag-input'=>true,'data-rule-taglength' => 330),'label_attr' => array()))
                ->add('tagsEn', formType\TextType::class, array('mapped' => false, 'required' => FALSE, 'attr' => array('data-tag-input'=>null,'data-rule-taglength' => 330),'label_attr' => array()))
                ->add('ingredients',  CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                ->add('ingredientsEn',CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                ->add('method',  CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                ->add('methodEn',CKEditorType::class, array('required' => TRUE,'attr' => array('dev-full-width-widget'=>true,'data-validate-element'=>true,'data-rule-ckmin' => 10,'data-error-after-selector' => '.dev-after-element')))
                ->add('chef', DocumentType::class, array('required' => false,'placeholder' => 'اختار الشيف','class' => 'IbtikarGlanceUMSBundle:Staff', 'attr' => array('data-img-method'=>'webPath','data-img-default'=>'bundles/ibtikarshareeconomydashboarddesign/images/profile.jpg','class' => 'select-with-thumb')))
                ->add('preparationTime', formType\TextType::class, array('required' => false, 'attr' => array()))
                ->add('cookingTime', formType\TextType::class, array('required' => false, 'attr' => array()))
                ->add('servingCount', formType\TextType::class, array('required' => false, 'attr' => array()))
                ->add('difficulty', formType\ChoiceType::class, array('required' => FALSE,
                    'choices' => Recipe::$difficultyMap,
                    'expanded' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe'
                ))
                ->add('course', formType\ChoiceType::class, array('required' => FALSE,
                    'choices' => array_flip(Recipe::$courseMap),
                    'multiple' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe'
                ))
                ->add('meal', formType\ChoiceType::class, array('required' => FALSE,
                    'choices' => array_flip(Recipe::$mealMap),
                    'multiple' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe'
                ))
                ->add('keyIngredient', formType\ChoiceType::class, array('required' => FALSE,
                    'choices' => array_flip(Recipe::$keyIngredientMap),
                    'multiple' => true, 'placeholder' => false, 'empty_data' => null,'choice_translation_domain'=>'recipe'
                ))
                ->add('products', DocumentType::class, array('required' => false,'multiple' => 'multiple','placeholder' => 'Choose Product','class' => 'IbtikarGlanceDashboardBundle:Product', 'attr' => array('data-img-method'=>'profilePhoto','data-img-default'=>'bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg','class' => 'select-with-thumb')))
                ->add('submitButton', formType\SubmitType::class);
    }


    /**
     * @return string
     */
    public function getName() {
        return 'recipe_type';
    }

}
