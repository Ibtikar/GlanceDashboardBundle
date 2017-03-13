<?php

namespace Ibtikar\GlanceDashboardBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class DateTimeTransformer implements DataTransformerInterface {


    public function reverseTransform($value) {
        return $value;
    }

    public function transform($value) {
        if($value && is_object($value)) {
            return $value->format('m/d/Y');
        }
        return $value;
    }
}
