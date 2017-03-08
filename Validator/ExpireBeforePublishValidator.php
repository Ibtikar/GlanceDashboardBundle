<?php

namespace Ibtikar\GlanceDashboardBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExpireBeforePublishValidator extends ConstraintValidator
{
    public function validate($protocol, Constraint $constraint)
    {
        if($protocol->getAutoPublishDate()) {
                if ($protocol->getAutoPublishDate() > $protocol->getExpiryDate()) {
                    $this->context->addViolationAt(
                        "expiryDate",
                        $constraint->message
                    );
                }
        }
    }
}