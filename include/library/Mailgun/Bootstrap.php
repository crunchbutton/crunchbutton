<?php

namespace Mailgun;

/**
 * Bootstrap class that facilitates autoloading.  A naive
 * PSR-0 autoloader.
 *
 * @author Nate Good <me@nategood.com>
 */
class Bootstrap
{

    const DIR_GLUE = DIRECTORY_SEPARATOR;
    const NS_GLUE = '\\';

    public static $registered = false;

    /**
     * Register the autoloader and any other setup needed
     */
    public static function init()
    {
        spl_autoload_register(array('\Mailgun\Bootstrap', 'autoload'));
    }

    /**
     * The autoload magic (PSR-0 style)
     *
     * @param string $classname
     */
    public static function autoload($classname)
    {
        self::_autoload(dirname(dirname(__FILE__)), $classname);
    }

    /**
     * Register the autoloader and any other setup needed
     */
    public static function pharInit()
    {
        spl_autoload_register(array('\Mailgun\Bootstrap', 'pharAutoload'));
    }

    /**
     * Phar specific autoloader
     *
     * @param string $classname
     */
    public static function pharAutoload($classname)
    {
        self::_autoload('phar://Mailgun.phar', $classname);
    }

    /**
     * @param string base
     * @param string classname
     */
    private static function _autoload($base, $classname)
    {
	    echo $classname;
        $parts      = explode(self::NS_GLUE, $classname);
        $path       = $base . self::DIR_GLUE . implode(self::DIR_GLUE, $parts) . '.php';

        if (file_exists($path)) {
            require_once($path);
        }
    }

}
