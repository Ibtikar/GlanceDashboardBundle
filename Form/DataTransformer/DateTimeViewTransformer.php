<?php

namespace Ibtikar\GlanceDashboardBundle\Form\DataTransformer;

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class DateTimeViewTransformer extends DateTimeTransformer {

    public function reverseTransform($value) {
        return date(\DateTime::ATOM,strtotime($value));
    }


    public function transform($value) {
//        die(var_dump(parent::transform($value)." ".date('g:i A',  strtotime($value))));
        if($value) {
            return parent::transform($value)." ".date('g:i A',  strtotime($value));
        }
    }

}
