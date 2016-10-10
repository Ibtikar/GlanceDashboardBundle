<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RoleController extends BackendController {

    protected $translationDomain = 'role';
    protected  $tabs=array('user'=>array('title'=>'user','modules'=>array('STAFF'=>array(),'VISITOR'=>array()),'permission'=>array()),
        'city'=>array('title'=>'city','modules'=>array('CITY'=>array()),'permission'=>array()));
    protected $tabsnames=array('user','city');
    private $internalPermissions = array(
        'ROLE_ADMIN', 'ROLE_STAFF',

        );

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array("tooltip"=>"PermissionsDisplayText"),
            "permissionscount" => array(),
            "createdAt" => array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "name",
            "permissionscount",
            "createdAt"
        );
    }

    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setActions(array ("Add","Edit","Delete"));
        $this->listViewOptions->setBulkActions(array("Delete"));
        $this->listViewOptions->setTemplate("IbtikarBackendBundle:Role:list.html.twig");
    }


    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $id
     * @return type
     */
    public function editAction(Request $request, $id) {
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('backend-home', $this->generateUrl('backend_home'));
        $breadcrumbs->addItem('List Role', $this->generateUrl('role_list'));
        $breadcrumbs->addItem('Edit Role', $this->generateUrl('role_edit', array('id' => $id)));

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $permissions = $this->customPermissionsArray($this->container->getParameter('permissions'));
        $dm = $this->get('doctrine_mongodb')->getManager();
        $role = $dm->getRepository('IbtikarBackendBundle:Role')->find($id);
        if(!$role) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $permissionExist = FALSE;
        if (in_array('ROLE_CONTACTGROUP_VIEW', array_values($role->getPermissions()))) {
            $permissionExist = TRUE;
        }
        $form = $this->buildForm($role, $permissions);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dm->flush();
                $groupId = array();
                if ($permissionExist && !in_array('ROLE_CONTACTGROUP_VIEW', array_values($role->getPermissions()))) {
                    $groups = $dm->getRepository('IbtikarBackendBundle:Group')->findBy(array('roles' => $role->getId()));
                    foreach ($groups as $group) {
                        $groupId[] = $group->getId();
                    }

                    if (!empty($groupId)) {
                        $staffMembersQuery = $dm->createQueryBuilder('IbtikarBackendBundle:Staff')->field('deleted')->equals(false);
                        $staffMembers = $staffMembersQuery->addAnd(
                                        $staffMembersQuery
                                                ->expr()
                                                ->addOr(
                                                        $staffMembersQuery
                                                        ->expr()
                                                        ->field('role')
                                                        ->equals($role->getId())
                                                )
                                                ->addOr(
                                                        $staffMembersQuery
                                                        ->expr()
                                                        ->field('group')
                                                        ->in($groupId)
                                        ))->getQuery()->execute();
                    } else {
                        $staffMembers = $dm->createQueryBuilder('IbtikarBackendBundle:Staff')->field('deleted')->equals(false)
                                        ->field('role')
                                        ->equals($role->getId())->getQuery()->execute();
                    }
                    foreach ($staffMembers as $staffMember) {
                     $staffMembers = $dm->createQueryBuilder('IbtikarBackendBundle:ContactGroup')
                                        ->update()
                                        ->multiple(true)
                                        ->field('staff')->exists(TRUE)->field('staff')->equals($staffMember->getId())
                                        ->field('staff')->pull($staffMember->getId())
                                        ->getQuery()->execute();
                    }
                }
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }
        return $this->render('IbtikarBackendBundle:Role:edit.html.twig', array(
                    'entity' => $role,
                    'form' => $form->createView(),
                    'translationDomain' => $this->translationDomain
        ));
    }

    public function createAction(Request $request) {

        $breadCrumb= new \stdClass();
        $breadCrumb->active=true;
        $breadCrumb->link= $this->generateUrl('ibtikar_glance_dashboard_role_create');
        $breadCrumb->linkType= 'add';
        $breadCrumb->text= $this->trans('role create',array(),  $this->translationDomain);

        $permissions = $this->container->getParameter('permissions');
        $permissions = $this->customPermissionsArray($permissions);
        $role = new Role();

        $form = $this->buildForm($role, $permissions);
        $permissionSelected=array();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            $formData = $request->get('form');
            $permissionSelected=$formData['permissions'];
            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $role->setPermissionscount(count($role->getPermissions()));
                $dm->persist($role);
                $dm->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('done sucessfully'));

                return $this->redirect($request->getUri());
            }
        }

        return $this->render('IbtikarGlanceDashboardBundle:Role:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb'=>array($breadCrumb),
                    'title'=>$this->trans('Add new role',array(),  $this->translationDomain),
                    'tabs'=> $this->tabs,
                    'permissionSelected'=>$permissionSelected,
                    'translationDomain' => $this->translationDomain
        ));
    }

    private function customPermissionsArray(Array $array) {
        $validArray = array();

        foreach ($this->internalPermissions as $permission) {
            unset($array[$permission]);
        }
        $translator = $this->get('translator');
        $module=array();
        foreach ($array as $key => $value) {
            $permission = explode(" ",str_replace("_", " ", strtolower(substr($key, 5))));
            foreach ($this->tabsnames as $tabsName ) {
            if(isset($this->tabs[$tabsName]['modules'][strtoupper($permission[0])])){
                if(!in_array($permission[1],$this->tabs[$tabsName]['permission'])){
                   $this->tabs[$tabsName]['permission'][]= strtoupper($permission[1]);
                   $this->tabs[$tabsName]['permission']=  array_unique($this->tabs[$tabsName]['permission']);


                }
                $this->tabs[$tabsName]['modules'][strtoupper($permission[0])][]=$key;
            }
             }
            $value = $translator->trans($permission[0], array("wordByWord"=>true,"flip"=>true), $this->translationDomain)." ".$permission[1];
            $validArray[$key] = $value;

        }

        return array_keys($validArray);
    }


    private function buildForm($role, $permissions) {
        return $this->createFormBuilder($role, array('translation_domain' => $this->translationDomain,'attr'=>array('class'=>'dev-page-main-form dev-js-validation form-horizontal')))
                        ->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class,array('attr' => array('data-rule-unique' => 'ibtikar_glance_dashboard_role_check_field_unique','data-msg-unique'=>  $this->trans('not valid'),'data-name'=>'name','data-rule-maxlength' => 330,'data-url'=>  $this->generateUrl('ibtikar_glance_dashboard_role_check_field_unique'),'placeholder'=>'')))
                        ->add('description', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('required' => false))
                        ->add('permissions', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                            'choices' => $permissions,
                            'multiple' => true,
                            'expanded' => true,
                            'attr' => array(
                                "data-msg-mincheck" => $this->get('translator')->trans('You must have at least 1 Permission', array(), $this->translationDomain),
                                "data-rule-mincheck"=> "1",
                                "data-error-after-selector"=>".dev-page-main-form .table-responsive"
                            )
                        ))
                        ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                        ->getForm();
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Document $document
     * @return string
     */
    protected function validateDelete(Document $document) {
        $groups = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarBackendBundle:Group')
                        ->field('roles')->equals($document->getId())
                        ->field('rolescount')->equals(1)
                        ->getQuery()->execute();
        $groupNames = array();
        foreach ($groups as $group) {
            $groupNames[] = $group->__toString();
        }
        if (count($groupNames) > 0) {
            return $this->trans(str_replace('%s', implode(',', $groupNames), $this->trans("can't delete this role because groups %s contain only this role")));
        }
    }

}
