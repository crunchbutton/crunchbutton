<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class IdentityVerificationFailed extends Error
{
    public static $codes = array('identity-verification-error', 'business-principal-kyc', 'business-kyc', 'person-kyc');
}
