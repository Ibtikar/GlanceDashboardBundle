<?php

namespace Ibtikar\GlanceDashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Ibtikar\GlanceDashboardBundle\Document\HomeBanner;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class PageType extends AbstractType {

 

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder->add('description', formType\TextareaType::class, array('required' => FALSE))
                ->add('descriptionEn', formType\TextareaType::class, array('required' => FALSE));

        if ($options['attr']['shortName'] == 'MagazineBannar') {
            $builder->add('bannerUrl', formType\TextType::class, array('required' => true, 'attr' => array('data-validate-element' => true)));
        }
        $builder->add('save', formType\SubmitType::class);

      
    }


    /**
     * @return string
     */
    public function getName() {
        return 'page_type';
    }

    public function configureOptions( \Symfony\Component\OptionsResolver\OptionsResolver $resolver ) {
    $resolver->setDefaults( [
      'shortName' => '',


        ]);
    }

}
