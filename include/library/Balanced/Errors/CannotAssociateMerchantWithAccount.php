<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class CannotAssociateMerchantWithAccount extends Error
{
    public static $codes = array('cannot-associate-merchant-with-account');
}