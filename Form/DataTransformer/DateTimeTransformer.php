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
        if($value) {
//            die(var_dump($value->format('d/m/Y g:i A')));
            return $value->format('d/m/Y g:i A');
        }
    }
}
