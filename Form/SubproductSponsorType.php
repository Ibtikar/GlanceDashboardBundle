<?php

namespace Ibtikar\GlanceDashboardBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as formType;


class SubproductSponsorType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('id', formType\HiddenType::class)
                ->add('name', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3)))
                ->add('nameEn', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3)))
                ->add('price', formType\TextType::class, array('attr' => array()))
                ->add('website', formType\TextType::class, array('attr' => array('data-rule-minlength' => 3)))
//                ->add('file', formType\FileType::class, array('label' => 'image', 'attr' => array('class' => 'dev-image-input', 'accept' => '.jpg,.jpeg,.png', 'data-error-after-selector' => '.fileupload','data-msg-accept' => 'يجب ان تكون الصورة بصيغة JPG أو JPEG أو PNG', 'data-rule-filesize' => '4', 'data-rule-dimensions' => '200', 'data-error-right' => '')));
               ->add('file', formType\FileType::class, array('required' => TRUE,
                'attr' => array('accept' => 'image/jpg,image/jpeg,image/png', 'data-msg-accept' => 'يجب ان تكون الصورة بصيغة JPG أو JPEG أو PNG',
                    'data-error-after-selector' => '.uploadCoverImg', 'data-rule-filesize' => 2,
                    'data-rule-dimensions' => '200',
                    'data-msg-dimensions'=>'يجب الا تقل ابعاد الصورة عن 200*200',
                    'data-msg-filesize'=>'يجب الا يزيد حجم الصوره عن 2 ميجا',
                    'data-image-url' => $builder->getForm()->getData() ? $builder->getForm()->getData()->getWebPath(): '')));
        }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => \Ibtikar\GlanceDashboardBundle\Document\Sponsor::class,
        ));
    }

    public function getName() {
        return 'subproduct_sponsor_type';
    }

}
