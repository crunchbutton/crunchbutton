<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class CannotCredit extends Error
{
    public static $codes = array('funding-destination-not-creditable');
}
