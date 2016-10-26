<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;


class PhoneType extends AbstractType {

    private $countries = array();

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('phone', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array('attr'=>array('data-rule-mobile'=>TRUE,'data-msg-mobile'=>'test','data-error-after-selector'=>'.intl-tel-input')))
                ->add("countryCode",  \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array('attr' => array('parent-class'=>'hidden'),'required'=>FALSE));
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ibtikar\GlanceDashboardBundle\Document\Phone'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'phone';
    }
}