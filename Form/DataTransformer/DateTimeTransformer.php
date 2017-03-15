<?php

namespace Ibtikar\GlanceDashboardBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class DateTimeTransformer implements DataTransformerInterface {


    public function reverseTransform($value)
    {
        if ($value) {
            return \DateTime::createFromFormat('m/d/Y', $value)->modify('midnight');
        }
        return $value;
    }

    public function transform($value) {
        if($value && is_object($value)) {
            return $value->modify('midnight')->format('m/d/Y');
        }
        return $value;
    }
}
