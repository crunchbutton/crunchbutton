<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class Declined extends Error
{
    public static $codes = array('funding-destination-declined', 'authorization-failed', 'card-declined');
}
