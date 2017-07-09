<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ibtikar\GlanceDashboardBundle\Document\Product;

class ProductType extends AbstractType {

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
//     ->add('coverType', formType\ChoiceType::class, array('choices' => Product::$coverTypeChoices, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#product_type_coverType')))
                ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 3)))
                ->add('bannerUrl', formType\TextType::class, array('required' => FALSE))
//            ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
//            ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('about', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('aboutEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 10)))
                ->add('relatedTip', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
                    'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related_tip', 'ajax-url-var' => 'relatedTipSearchUrl')
                ))
                ->add('relatedArticle', formType\ChoiceType::class, array('multiple' => true, 'required' => FALSE, 'mapped' => FALSE,
                    'choice_translation_domain' => 'recipe', 'attr' => array('class' => 'select-ajax', 'data_related_container' => 'form_related_article', 'ajax-url-var' => 'relatedArticleSearchUrl')))
                ->add('related_tip', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
                ->add('related_article', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
//            ->add('minimumRelatedRecipe', formType\HiddenType::class, array('required' => true, 'attr' => array('data-msg-required' => ' '), 'mapped' => FALSE))
                ->add('media', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
                ->add('submitButton', formType\HiddenType::class, array('required' => FALSE, "mapped" => false))
                ->add('save', formType\SubmitType::class);
    }

    /**
     * @return string
     */
    public function getName() {
        return 'product_type';
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        $resolver->setDefaults([
            'type' => 'create',
        ]);
    }

}
