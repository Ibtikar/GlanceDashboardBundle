<?php

namespace Ibtikar\GlanceDashboardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InternationalPhone extends Constraint
{
    public $message = 'phone must be in the right format';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy() {
        return "international-phone";
    }
}
