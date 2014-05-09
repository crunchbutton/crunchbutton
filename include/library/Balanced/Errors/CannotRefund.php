<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class CannotRefund extends Error
{
    public static $codes = array('funding-source-not-refundable');
}
