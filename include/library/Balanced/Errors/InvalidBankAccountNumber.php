<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class InvalidBankAccountNumber extends Error
{
    public static $codes = array('invalid-bank-account-number');
}
