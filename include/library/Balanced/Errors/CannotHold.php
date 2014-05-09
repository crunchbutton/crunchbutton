<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class CannotHold extends Error
{
    public static $codes = array('funding-source-not-hold');
}
