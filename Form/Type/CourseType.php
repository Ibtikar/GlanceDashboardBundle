<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Ibtikar\GlanceDashboardBundle\Form\DataTransformer\DateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Ibtikar\GlanceDashboardBundle\Document\Course;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class CourseType extends AbstractType {

    private $extraOptions = array();
    private $isNew;


    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->isNew = $options['isNew'];

        $transformer = new DateTimeTransformer();

        $date = new \DateTime();

        $minDate = $date->modify('+1 hour')->format(\DateTime::ISO8601);

        $builder
                ->add('name', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('nameEn', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3,'data-rule-maxlength' => 150)))
                ->add('youtubeChannel', formType\UrlType::class, array('required' => true));

//        if($this->isNew){
            $builder->add('questions', formType\CollectionType::class, array('label' => false,'entry_type' => CourseQuestionType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'courseQuestion')));
            $builder->add('questionsEn', formType\CollectionType::class, array('label' => false,'entry_type' => CourseQuestionType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => true, 'attr' => array('class' => 'courseQuestion')));
//        }



        $builder->add('save', formType\SubmitType::class);
    }

    public function getName() {
        return 'course_type';
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
