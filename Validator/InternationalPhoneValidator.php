<?php

namespace Ibtikar\GlanceDashboardBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use libphonenumber\PhoneNumberUtil;

class InternationalPhoneValidator extends ConstraintValidator
{
    public function validate($protocol, Constraint $constraint)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        if($protocol->getMobile() && $protocol->getMobile()->getPhone()&& $protocol->getMobile()->getCountryCode()) {
            try {
                $phoneNumber = $phoneUtil->parse($protocol->getMobile()->getPhone(), strtoupper($protocol->getMobile()->getCountryCode()));
                if (false === $phoneUtil->isValidNumber($phoneNumber)) {
                    $this->context->addViolationAt(
                        "mobile",
                        $constraint->message
                    );
                }
            } catch (\Exception $e) {
                $this->context->addViolationAt(
                    "mobile",
//                    $constraint->message. " => " .$e->getMessage()
                    $constraint->message
                );
            }
        }
    }
}