<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/**
 * A Callback is a publicly accessible location that can receive JSON
 * payloads whenever an Event occurs.
 *
 * <code>
 * $callback = new Balanced\Callback(array(
 *   "url" => "http://www.example.com/callback"
 * ));
 * $callback->save();
 * </code>
 */
class Callback extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('callbacks', 'id', '/');
        self::$_registry->add(get_called_class());
    }
}
