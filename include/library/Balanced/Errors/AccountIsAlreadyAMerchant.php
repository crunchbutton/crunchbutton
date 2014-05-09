<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class AccountIsAlreadyAMerchant extends Error
{
    public static $codes = array('account-already-merchant');
}
