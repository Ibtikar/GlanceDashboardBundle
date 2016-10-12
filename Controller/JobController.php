<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Job;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;

class JobController extends BackendController {

    protected $translationDomain = 'job';

    protected function configureListColumns() {
        $this->allListColumns = array(
            "title" => array(),
            "title_en" => array(),
            "staffMembersCount" => array('type' => 'number'),
            "createdAt" => array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "title",
            "staffMembersCount",
            "createdAt"
        );
    }

    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setDefaultSortBy("title");
        $this->listViewOptions->setDefaultSortOrder("asc");

        $this->listViewOptions->setActions(array ("Add","Edit","Delete"));
        $this->listViewOptions->setBulkActions(array("Delete"));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createAction(Request $request) {
        $breadCrumb= new \stdClass();
        $breadCrumb->active=true;
        $breadCrumb->link= $this->generateUrl('ibtikar_glance_dashboard_job_create');
        $breadCrumb->linkType= 'add';
        $breadCrumb->text= $this->trans('Add new job',array(),  $this->translationDomain);
        $dm = $this->get('doctrine_mongodb')->getManager();

        $job = new Job();
        $form = $this->createFormBuilder($job, array('translation_domain' => $this->translationDomain,'attr'=>array('class'=>'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('title',null, array('required' => true,'attr' => array('data-rule-maxlength' => 150,'data-rule-unique' => 'ibtikar_glance_dashboard_job_check_field_unique','data-name'=>'title','data-msg-unique'=>  $this->trans('not valid'),'data-rule-maxlength' => 150,'data-url'=>$this->generateUrl('ibtikar_glance_dashboard_job_check_field_unique'))))
                ->add('title_en',null, array('required' => true,'attr' => array('data-rule-maxlength' => 150,'data-rule-unique' => 'ibtikar_glance_dashboard_job_check_field_unique','data-name'=>'title_en','data-msg-unique'=>  $this->trans('not valid'),'data-rule-maxlength' => 150,'data-url'=>$this->generateUrl('ibtikar_glance_dashboard_job_check_field_unique'))))
                ->add('save', formType\SubmitType::class)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->persist($job);
                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Job:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb'=>array($breadCrumb),
                    'title'=>$this->trans('Add new job',array(),  $this->translationDomain),
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request,$id) {
        //build breadcrumb
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem('backend-home', $this->generateUrl('backend_home'));
        $breadcrumbs->addItem('List Job', $this->generateUrl('job_list'));
        $breadcrumbs->addItem('Edit Job', $this->generateUrl('job_edit',array('id' => $id)));

        //prepare form
        $job = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarBackendBundle:Job')->find($id);
        if(!$job || in_array($job->getTitleEn(), Job::$systemEnglishJobTitles)) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $form = $this->createFormBuilder($job, array('translation_domain' => $this->translationDomain))
                ->add('title',null, array('attr' => array('data-rule-unique' => 'job_check_field_unique','data-name'=>'title','data-rule-maxlength' => 330)))
                ->add('title_en',null, array('attr' => array('data-rule-unique' => 'job_check_field_unique','data-name'=>'title_en','data-rule-maxlength' => 330)))
                ->add('save', 'submit')
                ->getForm();

        //handle form submission
        if ($request->getMethod() === 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($job);
                $dm->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));

                return $this->redirect($request->getUri());
            }
        }

        //return template
        return $this->render('IbtikarBackendBundle:Job:edit.html.twig', array(
                    'form' => $form->createView(),
                    'translationDomain' => $this->translationDomain
        ));
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Document $document
     * @return string
     */
    protected function validateDelete(Document $document) {
        if(in_array($document->getTitleEn(), Job::$systemEnglishJobTitles)) {
            return $this->get('translator')->trans('failed operation');
        }
        if ($document->getStaffMembersCount() > 0) {
            return $this->trans('Cant deleted,it contain staff members');
        }
    }

}
