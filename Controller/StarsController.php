<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
class StarsController extends BackendController {

    protected $translationDomain = 'stars';

    public function editAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $settings = $this->get('systemSettings')->getSettingsRecordsByCategory('stars');
        $settingsArray = $this->convertRecordsToArray($settings);

        if(count($settingsArray) != 4){
            throw new \Exception("Settings fixtures are not loaded.");
        }

        $form = $this->createFormBuilder(null,array('translation_domain' => $this->translationDomain,'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
        ->add('briefAr',  formType\TextareaType::class, array('data'=> $settingsArray["stars-brief-ar"],'required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
        ->add('briefEn',formType\TextareaType::class, array('data'=>$settingsArray["stars-brief-en"],'required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10)))
        ->add('benefitsAr',  CKEditorType::class, array('data'=>$settingsArray["stars-benefits-ar"],'required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10,'data-rule-ckmin' => 10,'data-rule-ckmax' => 1000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
        ->add('benefitsEn',CKEditorType::class, array('data'=>$settingsArray["stars-benefits-en"],'required' => true,'attr' => array('data-validate-element'=>true,'data-rule-maxlength' => 1000,'data-rule-minlength' => 10,'data-rule-ckmin' => 10,'data-rule-ckmax' => 1000,'data-rule-ckreq' => true,'data-error-after-selector' => '.dev-after-element')))
        ->getForm();

        $image = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Media')->findOneBy(array(
            'collectionType' => 'Stars'
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            if ($form->isValid()){
                foreach ($settings as $record) {
                    switch ($record->getKey()) {
                        case "stars-brief-ar":
                            $record->setValue($data['briefAr']);
                            break;
                        case "stars-brief-en":
                            $record->setValue($data['briefEn']);
                            break;
                        case "stars-benefits-ar":
                            $record->setValue($data['benefitsAr']);
                            break;
                        case "stars-benefits-en":
                            $record->setValue($data['benefitsEn']);
                    }
                }

                $dm->flush();

                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Stars:edit.html.twig', array(
            'form' => $form->createView(),
            'translationDomain' => $this->translationDomain,
            'title' => $this->trans('Edit Stars', array(), $this->translationDomain),
            'coverImage' => $image
        ));
    }

    private function convertRecordsToArray($settingsRecords) {
        $settings = array();
        foreach ($settingsRecords as $record) {
            $settings[$record->getKey()] = $record->getValue();
        }
        return $settings;
    }
}
