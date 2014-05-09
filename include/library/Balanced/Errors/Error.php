<?php

namespace Balanced\Errors;

use RESTful\Exceptions\HTTPError;

class Error extends HTTPError
{
    public static $codes = array();

    protected static function init()
    {
        foreach(glob(dirname(__FILE__).'/*.php') as $class_path) {
            require_once($class_path);
        }

        $errorClass = get_class();

        foreach (get_declared_classes() as $class) {
            if (get_parent_class($class) == $errorClass) {
                foreach ($class::$codes as $type)
                    self::$codes[$type] = $class;
            }
        }
    }

    public static function createFromResponse($response)
    {
        if (empty(self::$codes))
            self::init();

        //die(88);
        $err = $response->body->errors[0];

        $code = $err->category_code;

        if (isset(self::$codes[$code]))
            $cn = self::$codes[$code];
        else
            $cn = get_class();

        return new $cn($response);
    }
}
