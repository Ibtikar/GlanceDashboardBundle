<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Ibtikar\GlanceDashboardBundle\Form\DataTransformer\DateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class CompetitionType extends AbstractType {

    private $extraOptions = array();
    private $isNew;

    public function __construct($new = true,$extraOptions = array()) {
        $this->isNew = $new;
        $this->extraOptions = $extraOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $transformer = new DateTimeTransformer();
        $builder
                ->add('title', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('titleEn', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('brief', formType\TextareaType::class, array('attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 1000)))
                ->add('briefEn', formType\TextareaType::class, array('attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 1000)));

        if($this->isNew){
            $builder->add('questions', formType\CollectionType::class, array('label' => false,'entry_type' => QuestionType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'questionnaireQuestion')));
        }

        $builder->add($builder->create('expiryDate', formType\DateType::class, array('required' => false,'widget' => 'single_text', 'attr' => array('data-date-start-date' => isset($this->extraOptions['data-date-start-date'])?$this->extraOptions['data-date-start-date']:'+1d', 'data-rule-dateAfterToday' => '')))->addViewTransformer($transformer));

//        if($this->isNew){
//            $builder->add('allowedToVote', 'choice', array('choices' => Poll::$allowedVoters, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#questionnaire_type_allowedToVote')))
//                    ->add('resultsVisibility', 'choice', array('choices' => Poll::$resultsVisibilities, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#questionnaire_type_resultsVisibility')));
//        }
        $builder->add('save', formType\SubmitType::class);
    }

    public function getName() {
        return 'questionnaire_type';
    }

}
