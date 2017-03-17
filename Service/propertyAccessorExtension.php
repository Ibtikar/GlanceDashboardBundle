<?php
namespace Ibtikar\GlanceDashboardBundle\Service;


class propertyAccessorExtension extends \Twig_Extension
{

    private $translator;

    public function __construct($translator) {
        $this->translator = $translator;

       }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'property_access', array($this, 'propertyAccess')
            )
        );
    }

     public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('unserialize', array($this, 'unserializeFilter')),
        );
    }

    public function propertyAccess($object,$functionName,$fieldAccess)
    {
       $functionToBeCalled='get'.ucfirst($functionName);
       $functionToAccessField='get'.ucfirst($fieldAccess);
       $calledObject=$object->$functionToBeCalled();
       return $object->$functionToBeCalled()?method_exists($object->$functionToBeCalled(),$functionToAccessField)?$object->$functionToBeCalled()->$functionToAccessField():$object->$functionToBeCalled()->__toString() :'';
    }

    public function unserializeFilter($string)
    {
        return unserialize($string);
    }

}