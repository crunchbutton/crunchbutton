<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class AddressVerificationFailed extends Error
{
    public static $codes = array('address-verification-failed');
}
