<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class InvalidRoutingNumber extends Error
{
    public static $codes = array('invalid-routing-number');
}
