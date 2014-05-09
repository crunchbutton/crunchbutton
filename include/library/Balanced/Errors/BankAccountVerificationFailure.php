<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class BankAccountVerificationFailure extends Error
{
    public static $codes = array(
        'bank-account-authentication-not-pending',
        'bank-account-authentication-failed',
        'bank-account-authentication-already-exists'
    );
}
