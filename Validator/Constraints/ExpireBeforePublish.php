<?php

namespace Ibtikar\GlanceDashboardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExpireBeforePublish extends Constraint
{
    public $message = 'expire date must me after publish date';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy() {
        return "Ibtikar\GlanceDashboardBundle\Validator\ExpireBeforePublishValidator";
    }
}
