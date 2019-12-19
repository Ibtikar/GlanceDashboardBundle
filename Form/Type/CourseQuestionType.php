<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as formType;

class CourseQuestionType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('id', formType\HiddenType::class)
                ->add('question', formType\TextType::class, array('attr' => array('data-rule-maxlength' => 150, 'required' => '')))
                ->add('answers', formType\CollectionType::class, array('entry_type' => CourseQuestionChoiceAnswerType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'voteAnswer')));
   ; }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Ibtikar\GlanceDashboardBundle\Document\CourseQuestion',
        ));
    }

    public function getName() {
        return 'coursequestion_type';
    }

}
