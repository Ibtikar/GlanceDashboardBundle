<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Ibtikar\GlanceDashboardBundle\Document\Document;
use Ibtikar\GlanceDashboardBundle\Document\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type as formType;

class RoleController extends BackendController {

    protected $translationDomain = 'role';
    protected  $tabs=array('user'=>array('title'=>'user','count'=>0,'modules'=>array('STAFF'=>array(),'VISITOR'=>array(),'ROLE'=>array(),'JOB'=>array()),'permission'=>array()),
        'city'=>array('title'=>'city','count'=>0,'modules'=>array('CITY'=>array()),'permission'=>array()));
    protected $tabsnames=array('user','city');
    private $internalPermissions = array(
        'ROLE_ADMIN', 'ROLE_STAFF',

        );

    protected function configureListColumns() {
        $this->allListColumns = array(
            "name" => array("isClickable" => TRUE, 'class' => 'dev-role-getPermision'),
            "permissionscount" => array(),
            "createdAt" => array("type"=>"date"),
            "updatedAt" => array("type"=>"date")
        );
        $this->defaultListColumns = array(
            "name",
            "permissionscount",
            "createdAt",
            'updatedAt'
        );
    }

    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setActions(array ());
        $this->listViewOptions->setBulkActions(array());
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Role:list.html.twig");
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $id
     * @return type
     */
    public function editAction(Request $request, $id) {
        $breadCrumb= new \stdClass();
        $breadCrumb->active=true;
        $breadCrumb->link= $this->generateUrl('ibtikar_glance_dashboard_role_create');
        $breadCrumb->linkType= 'add';
        $breadCrumb->text= $this->trans('Add new role',array(),  $this->translationDomain);

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(array('status' => 'login'));
        }

        $permissions = $this->customPermissionsArray($this->container->getParameter('permissions'));
        $dm = $this->get('doctrine_mongodb')->getManager();
        $role = $dm->getRepository('IbtikarGlanceDashboardBundle:Role')->find($id);
        if(!$role || $role->getNotModified()) {
            throw $this->createNotFoundException($this->trans('Wrong id'));
        }
        $permissionSelected=$role->getPermissions();

        $form = $this->buildForm($role, $permissions);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
             $formData = $request->get('form');
            $permissionSelected=array_merge($permissionSelected,$formData['permissions']);
            if ($form->isValid()) {
                $role->setEditAt( new \DateTime());
                $dm->flush();
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));
                return $this->redirect($request->getUri());
            }
        }
        return $this->render('IbtikarGlanceDashboardBundle:Role:create.html.twig', array(
                    'form' => $form->createView(),
                    'breadcrumb'=>array($breadCrumb),
                    'title'=>$this->trans('edit role',array(),  $this->translationDomain),
                    'tabs'=> $this->tabs,
                    'permissionSelected'=>$permissionSelected,
                    'translationDomain' => $this->translationDomain
        ));
    }

    public function createAction(Request $request) {

        $breadCrumb= new \stdClass();
        $breadCrumb->active=true;
        $breadCrumb->link= $this->generateUrl('ibtikar_glance_dashboard_role_create');
        $breadCrumb->linkType= 'add';
        $breadCrumb->text= $this->trans('Add new role',array(),  $this->translationDomain);

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
                $this->addFlash('success', $this->get('translator')->trans('done sucessfully'));

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
                $this->tabs[$tabsName]['count']++;
            }
             }
            $value = $translator->trans($permission[0], array("wordByWord"=>true,"flip"=>true), $this->translationDomain)." ".$permission[1];
            $validArray[$key] = $value;

        }

        return array_keys($validArray);
    }

    private function adminPermissions($permissions)
    {
        $permissionArray = array();

      foreach ($this->internalPermissions as $permission) {
            unset($permissions[$permission]);
        }
        foreach ($permissions as $key=> $value) {
             $permissionArray[] = str_replace("_", " ", strtolower(substr($key, 5)));

        }
        return $permissionArray;
    }

    private function buildForm($role, $permissions)
    {
        return $this->createFormBuilder($role, array('translation_domain' => $this->translationDomain, 'attr' => array('class' => 'dev-page-main-form dev-js-validation form-horizontal')))
                ->add('name', formType\TextType::class, array('required' => true, 'attr' => array('data-rule-unique' => 'ibtikar_glance_dashboard_role_check_field_unique', 'data-msg-unique' => $this->trans('not valid'), 'data-name' => 'name',
                        'data-rule-maxlength' => 150,
                        'data-rule-minlength' => 3,
                        'data-url' => $this->generateUrl('ibtikar_glance_dashboard_role_check_field_unique'),
                        'placeholder' => '')))
                ->add('description', formType\TextareaType::class, array('required' => false, 'attr' => array('data-rule-maxlength' => 500)))
                ->add('permissions', formType\ChoiceType::class, array(
                    'choices' => $permissions,
                    'multiple' => true,
                    'expanded' => true,
                    'attr' => array(
                        "data-msg-mincheck" => $this->get('translator')->trans('You must have at least 1 Permission', array(), $this->translationDomain),
                        "data-rule-mincheck" => "1",
                        "data-error-after-selector" => ".dev-page-main-form .table-responsive"
                    )
                ))
                ->add('save', formType\SubmitType::class)
                ->getForm();
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Document $document
     * @return string
     */
    protected function validateDelete(Document $document)
    {
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

   public function getListJsonData($request)
    {
        $page=($request->get('page')) ?$request->get('page'):1;
        $columnSort=$request->get('sort')?$request->get('sort'):'createdAt';
        $columnDir=$request->get('columnDir')?$request->get('columnDir'):'desc';
        $limit=$request->get('limit')?$request->get('limit'):2;

        $sEcho = $request->request->get('sEcho') ? $request->request->get('sEcho') : 0;
        $roleCount = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:Role')
                ->getQuery()->count();
        $roles = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:Role');
        if ($columnSort && $columnDir) {
            $roles = $roles->sort($columnSort, $columnDir);
        }
        $roles = $roles->skip(($page-1)*$limit)->limit($limit)->getQuery()->execute();
        $rolesObjects = array();



        if ($this->listName) {
            $listName = $this->listName;
        } else {
            $listName = 'ibtikar_glance_dashboard_role_list';
        }
        $this->configureListColumns();
        $selectedColumns = $this->getCurrentColumns($listName);


        foreach ($roles as $role) {
            $oneRole = array();
            $oneRole['id'] = '<div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" class="styled" data-id=' . $role->getId() . '>
                                    </label>
                              </div>';
            foreach ($selectedColumns as $value) {
                $getfunction = "get" . ucfirst($value);
                if ($value == 'name') {
                    $oneRole[$value] = '<a class="dev-role-getPermision" href="javascript:void(0)" data-id="'.$role->getId().'">' . $role->$getfunction() . '</a>';
                } elseif ($role->$getfunction() instanceof \DateTime) {
                    $oneRole[$value] = $role->$getfunction() ? $role->$getfunction()->format('Y-m-d') : null;
                } else {
                    $oneRole[$value] = $role->$getfunction();
                }
            }
            $rolesObjects[] = $oneRole;
        }
        return new JsonResponse(array('data' => $rolesObjects, "draw" => (int) $sEcho, 'sEcho' => (int) $sEcho,
            "recordsTotal" => $roleCount,
            "recordsFiltered" => $roleCount));
    }

    public function showRolePermissionAction(Request $request){

        $id= $request->get('id');

        $role = $this->get('doctrine_mongodb')->getManager()->getRepository('IbtikarGlanceDashboardBundle:Role')->find($id);

        if(!$role){

        }
//        var_dump($role->getName());
//        exit;
        if($role->getName()=='Admin'){
        $allPermissions = $this->container->getParameter('permissions');
        $permissions = $this->adminPermissions($allPermissions);
        }else{
            $permissions= $role->getPermissionsDisplayText();
        }
        return $this->render('IbtikarGlanceDashboardBundle:Role:rolePermision.html.twig', array("permisions" => $permissions, 'translationDomain' => $this->translationDomain));

    }
}
