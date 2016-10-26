<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;


class PhoneType extends AbstractType {


    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct($container) {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('phone', \Symfony\Component\Form\Extension\Core\Type\TextType::class,
                        array('attr'=>array('data-rule-mobile'=>TRUE,'data-msg-mobile'=>'test',
                            'data-error-after-selector'=>'.intl-tel-input',
                            'data-name'=>'mobile',
                            'data-country-code'=>"#staff_mobile_phone",
                            'data-rule-unique' => 'ibtikar_glance_ums_staff_check_field_unique',
                            'data-url' => $this->container->get('router')->generate('ibtikar_glance_ums_staff_check_field_unique'))))
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