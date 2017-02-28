<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as formType;

class QuestionType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('id', formType\HiddenType::class)
                ->add('question', formType\TextType::class, array('attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 330, 'required' => '')))
                ->add('answerImportance', formType\ChoiceType::class, array('choices' => \Ibtikar\GlanceDashboardBundle\Document\Question::$answerImportanceType, 'expanded' => true))
                ->add('questionType', formType\ChoiceType::class, array('expanded' => false,'choices' => \Ibtikar\GlanceDashboardBundle\Document\Question::$questionTypes,'attr'  => array('data-form-group-class' => 'bgGrey')))
                ->add('answerDisplay', formType\ChoiceType::class, array('choices' => \Ibtikar\GlanceDashboardBundle\Document\Question::$answerDisplayType, 'expanded' => true))
                ->add('resultDisplay', formType\ChoiceType::class, array('choices' => \Ibtikar\GlanceDashboardBundle\Document\Question::$resultDisplayType, 'expanded' => true))
                ->add('answers', formType\CollectionType::class, array('entry_type' => QuestionChoiceAnswerType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'voteAnswer')));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Ibtikar\GlanceDashboardBundle\Document\Question',
        ));
    }

    public function getName() {
        return 'question_type';
    }

}
