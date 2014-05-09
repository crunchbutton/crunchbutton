<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/*
 * A dispute occurs when a customer disputes a transaction that
 * occurred on their funding instrument.
 */

class Dispute extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('disputes', 'id', '/');
        self::$_registry->add(get_called_class());
    }
}
