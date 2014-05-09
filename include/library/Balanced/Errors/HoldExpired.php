<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class HoldExpired extends Error
{
    public static $codes = array('authorization-expired');
}
