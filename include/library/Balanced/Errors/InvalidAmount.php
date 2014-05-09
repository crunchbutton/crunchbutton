<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class InvalidAmount extends Error
{
    public static $codes = array('invalid-amount');
}
