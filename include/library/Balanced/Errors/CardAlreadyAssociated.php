<?php

namespace Balanced\Errors;

use Balanced\Errors\Error;

class CardAlreadyAssociated extends Error
{
    public static $codes = array('card-already-funding-src');
}
