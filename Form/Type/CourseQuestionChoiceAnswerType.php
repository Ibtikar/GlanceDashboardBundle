<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as formType;

class CourseQuestionChoiceAnswerType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('id', formType\HiddenType::class)
                ->add('correctAnswer',null,array('required'=>FALSE))

                ->add('answer', formType\TextType::class, array('attr' => array('data-rule-minlength' => 2,'data-rule-maxlength' => 150,'data-error-after-selector' => '.dev-question-answer-container')))
  ;  }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Ibtikar\GlanceDashboardBundle\Document\CourseQuestionChoiceAnswer',
        ));
    }

    public function getName() {
        return 'coursequestion_choice_answer_type';
    }

}
