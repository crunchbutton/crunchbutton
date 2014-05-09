<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class CannotDebit extends Error
{
    public static $codes = array('funding-source-not-debitable');
}
