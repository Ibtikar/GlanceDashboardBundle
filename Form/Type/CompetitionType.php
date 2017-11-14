<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Ibtikar\GlanceDashboardBundle\Form\DataTransformer\DateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Ibtikar\GlanceDashboardBundle\Document\Competition;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class CompetitionType extends AbstractType {

    private $extraOptions = array();
    private $isNew;


    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->isNew = $options['isNew'];

        $transformer = new DateTimeTransformer();

        $date = new \DateTime();

        $minDate = $date->modify('+1 hour')->format(\DateTime::ISO8601);

        $builder
                ->add('title', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('titleEn', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('SecondaryTitle', formType\TextType::class, array('required'=>FALSE,'attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('SecondaryTitleEn', formType\TextType::class, array('required'=>FALSE,'attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('brief', formType\TextareaType::class, array('required'=>FALSE,'attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 1000)))
                ->add('briefEn', formType\TextareaType::class, array('required'=>FALSE,'attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 1000)))
                ->add('termsAndConditions',  CKEditorType::class, array('required' => true,'attr' => array('data-validate-element'=>true,'data-rule-ckmin' => 10,'data-rule-ckmax' => 2000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
                ->add('termsAndConditionsEn',  CKEditorType::class, array('required' => true,'config' => array('contentsLangDirection' => 'ltr'),'attr' => array('data-validate-element'=>true,'data-rule-ckmin' => 10,'data-rule-ckmax' => 2000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))

//                ->add('termsAndConditions', formType\TextareaType::class, array('attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 2000)))
//                ->add('termsAndConditionsEn', formType\TextareaType::class, array('attr' => array('data-rule-minlength' => 10,'data-rule-maxlength' => 2000)))
                ->add('video', formType\TextType::class, array('mapped'=>FALSE,'required'=>FALSE,'attr'=>array('data-rule-youtube'=>'data-rule-youtube')));

        if($this->isNew){
            $builder->add('questions', formType\CollectionType::class, array('label' => false,'entry_type' => QuestionType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'competitionQuestion')));
            $builder->add('questionsEn', formType\CollectionType::class, array('label' => false,'entry_type' => QuestionType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'competitionQuestion')));
        }

        $builder->add($builder->create('expiryDate', formType\TextType::class, array('required' => false, 'attr' => array('data-min-date'=>$minDate,'data-date-start-date' => isset($this->extraOptions['data-date-start-date'])?$this->extraOptions['data-date-start-date']:'+1d', 'data-rule-dateAfterToday' => '')))->addViewTransformer($transformer))
                ->add('coverType', formType\ChoiceType::class, array('choices' => Competition::$coverTypeChoices, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#competition_type_coverType')));

        if($this->isNew){
            $builder->add('allowedToVote', formType\ChoiceType::class, array('choices' => array_keys(Competition::$allowedVoters), 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#competition_type_allowedToVote')));
//                    ->add('resultsVisibility', 'choice', array('choices' => Poll::$resultsVisibilities, 'expanded' => true, 'attr'  => array('data-error-after-selector' => '#competition_type_resultsVisibility')));
        }
        $builder->add('save', formType\SubmitType::class);
    }

    public function getName() {
        return 'competition_type';
    }

/**
 * {@inheritdoc}
 */
public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
{
    $resolver->setDefaults(array(
        'isNew' => 'new',
    ));
}
}
