<?php

namespace Ibtikar\GlanceDashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ibtikar\GlanceDashboardBundle\Controller\base\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\GlanceDashboardBundle\Document\Category;
use Ibtikar\GlanceDashboardBundle\Document\Document;

class CategoryController extends BackendController {

    protected $translationDomain = 'category';
    private $validationTranslationDomain = 'validators';
    protected $calledClassName = 'Category';

    protected function getObjectShortName() {
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
        );
        $this->defaultListColumns = array(
            "order",
            "name",
            "nameEn",
            "slugEn",
            "slug"

        );
        $this->listViewOptions->setBundlePrefix("ibtikar_glance_dashboard_");
    }

    /**
     * @author Maisara Khedr
     */
    protected function configureListParameters(Request $request) {
        $this->listViewOptions->setActions(array());
        $this->listViewOptions->setBulkActions(array());
        $this->listViewOptions->setDefaultSortBy("order");
        $this->listViewOptions->setDefaultSortOrder("asc");

        $queryBuilder = $this->createQueryBuilder("IbtikarGlanceDashboardBundle")->field('parent')->equals(null);

        $this->listViewOptions->setListQueryBuilder($queryBuilder);
        $this->listViewOptions->setTemplate("IbtikarGlanceDashboardBundle:Category:list.html.twig");
    }



    /**
     *
     * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
     */
    public function sortAction(Request $request)
    {


        $dm = $this->get('doctrine_mongodb')->getManager();
        $categoryRepo = $dm->getRepository('IbtikarGlanceDashboardBundle:Category');
        $sort = $request->get('sort');
        if ($sort) {
            $this->updateCategoryOrder($categoryRepo, $sort);
            $dm->flush();
            return new JsonResponse(array('status' => 'success','message'=>  $this->trans('done sucessfully')));
        }

        return new JsonResponse(array('status' => 'fail'));
    }

    private function updateCategoryOrder($categoryRepo,$catArray){

        foreach ($catArray as $index => $catId) {
            $category = $categoryRepo->findOneBy(array('_id'=>$catId));
            if($category){
                $setPositionFn = "setOrder";
                $category->$setPositionFn($index+1);
            }
        }
    }



}
