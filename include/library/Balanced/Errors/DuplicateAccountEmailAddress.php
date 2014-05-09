<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class DuplicateAccountEmailAddress extends Error
{
    public static $codes = array('duplicate-email-address');
}
