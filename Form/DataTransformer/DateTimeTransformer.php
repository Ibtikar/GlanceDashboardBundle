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
            return implode('-', array_map(function($datePart) {
                $fmt = numfmt_create('en', \NumberFormatter::TYPE_INT32);
                $dateNumber = numfmt_parse($fmt, $datePart);
                if($dateNumber < 10) {
                    $dateNumber = 0 . $dateNumber;
                }
                return $dateNumber;
            }, explode('-', $value)));
        }
    }

}
