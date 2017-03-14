<?php

namespace Ibtikar\GlanceDashboardBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class DateTimeTransformer implements DataTransformerInterface {


    public function reverseTransform($value) {
        return \DateTime::createFromFormat('d/m/Y', $value)->modify('+24 hours')->modify('midnight');
    }

    public function transform($value) {
        if($value && is_object($value)) {
            return $value->modify('-24 hours')->modify('midnight')->format('d/m/Y');
        }
        return $value;
    }
}
