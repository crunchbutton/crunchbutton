<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class InsufficientFunds extends Error
{
    public static $codes = array('insufficient-funds');
}
