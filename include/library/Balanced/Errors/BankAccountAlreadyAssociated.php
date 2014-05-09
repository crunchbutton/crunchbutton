<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class BankAccountAlreadyAssociated extends Error
{
    public static $codes = array('bank-account-already-associated');
}
