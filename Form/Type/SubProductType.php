<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ibtikar\GlanceDashboardBundle\Document\SubProduct;
use Ibtikar\GlanceDashboardBundle\Form\SubproductSponsorType;

class SubProductType extends AbstractType {

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
                ->add('nameEn', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 150, 'data-rule-minlength' => 2)))
//                ->add('product', \Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType::class,array('required' => TRUE,
//                'class' => 'IbtikarGlanceDashboardBundle:Product', 'placeholder' => $this->trans('Choose product',array(),'subproduct'),
//                'attr' => array('class' => 'select', 'data-error-after-selector' => '.select2-container')
//        ))
                ->add('description', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))
                ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE, 'attr' => array('data-validate-element' => true, 'data-rule-maxlength' => 1000, 'data-rule-minlength' => 5)))

                ->add('weight', formType\TextType::class, array('required' => FALSE, 'attr' => array()))
                ->add('size', formType\TextType::class, array( 'required' => FALSE, 'attr' => array()))
                ->add('media', formType\TextareaType::class, array('required' => FALSE, "mapped" => false, 'attr' => array('parent-class' => 'hidden')))
                ->add('sponsors', formType\CollectionType::class, array('entry_type' => SubproductSponsorType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => TRUE, 'attr' => array('class' => 'competitionQuestion')))

                ->add('save', formType\SubmitType::class);
    }

    /**
     * @return string
     */
    public function getName() {
        return 'subproduct_type';
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        $resolver->setDefaults([
            'type' => 'create',
        ]);
    }

}
