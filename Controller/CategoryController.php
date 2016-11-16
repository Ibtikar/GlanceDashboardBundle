<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Document\Category;
use Ibtikar\GlanceDashboardBundle\Document\Document;

class CategoryController extends BackendController
{

    protected $translationDomain = 'category';
    private $validationTranslationDomain = 'validators';
    protected $calledClassName = 'Category';

    protected function getObjectShortName()
    {
        return 'IbtikarGlanceDashboardBundle:' . $this->calledClassName;
    }

    /**
     * @author Ola <ola.ali@ibtikar.net.sa>
     */
    protected function configureListColumns()
    {
        $this->allListColumns = array(
            "name" => array("isSortable" => false),
            "nameEn" => array("isSortable" => false),
            "slugEn" => array("isSortable" => false),
            "slug" => array("isSortable" => false),
            "order" => array("isSortable" => false),
            "subcategoryNo" => array("isSortable" => false, "isClickable" => TRUE, 'class' => 'dev-show-subcategory'),
        );
        $this->defaultListColumns = array(
            "order",
            "name",
            "nameEn",
            'subcategoryNo',
            "slugEn",
            "slug"
        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    protected function configureListParameters(Request $request)
    {
        $this->listViewOptions->setActions(array());
        $this->listViewOptions->setBulkActions(array());
        $this->listViewOptions->setDefaultSortBy("order");
        $this->listViewOptions->setDefaultSortOrder("asc");

        $queryBuilder = $this->createQueryBuilder("IbtikarGlanceDashboardBundle")->field('parent')->equals(null);

        $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Category:list.html.twig");
    }

    public function sortAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_CATEGORY_VIEW') && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $categoryRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Category');
        $sort = $request->get('sort');
        if ($sort) {
            $this->updateCategoryOrder($categoryRepo, $sort);
            $dm->flush();
            return new JsonResponse(array('status' => 'success', 'message' => $this->trans('done sucessfully')));
        }

        return new JsonResponse(array('status' => 'fail'));
    }

    public function sortSubCategoryAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_CATEGORY_VIEW') && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $categoryRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Category');
        $sort = $request->get('sort');
        if ($sort) {
            $this->updateCategoryOrder($categoryRepo, $sort);
            $dm->flush();
            return new JsonResponse(array('status' => 'success', 'message' => $this->trans('done sucessfully')));
        }

        return new JsonResponse(array('status' => 'fail'));
    }

    private function updateCategoryOrder($categoryRepo, $catArray)
    {

        foreach ($catArray as $index => $catId) {
            $category = $categoryRepo->findOneBy(array('_id' => $catId));
            if ($category) {
                $setPositionFn = "setOrder";
                $category->$setPositionFn($index + 1);
            }
        }
    }

    public function showSubcategoryAction(Request $request)
    {

        if (!$this->getUser()) {
            return $this->getLoginResponse();
        }
        $securityContext = $this->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_CATEGORY_VIEW') && !$securityContext->isGranted('ROLE_ADMIN')) {
            return $this->getAccessDeniedResponse();
        }
        $id = $request->get('id');
        $subcategories = $this->get('doctrine_mongodb')->getManager()->createQueryBuilder('IbtikarGlanceDashboardBundle:Category')
                ->field('parent')->equals(new \MongoId($id))
                ->sort('order', 'ASC')
                ->getQuery()->execute();
        if (!$subcategories) {

        }
        return $this->render('IbtikarGlanceDashboardBundle:Category:subcategoryShow.html.twig', array('translationDomain' => $this->translationDomain, 'subcategories' => $subcategories));
    }
}
