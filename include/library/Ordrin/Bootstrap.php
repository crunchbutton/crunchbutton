<?php

namespace Ordrin;

class Bootstrap
{
    const DIR_SEPARATOR = DIRECTORY_SEPARATOR;
    const NAMESPACE_SEPARATOR = '\\';

    public static $initialized = false;
    
    
    public static function init()
    {
        spl_autoload_register(array('\Ordrin\Bootstrap', 'autoload'));
        self::initializeResources();
    }
    
    public static function autoload($classname)
    {
        self::_autoload(dirname(dirname(__FILE__)), $classname);
    }
    
    public static function pharInit()
    {
        spl_autoload_register(array('\Ordrin\Bootstrap', 'pharAutoload'));
        self::initializeResources();
    }
    
    public static function pharAutoload($classname)
    {
        self::_autoload('phar://ordrin.phar', $classname);
    }
    
    private static function _autoload($base, $classname)
    {
        $parts = explode(self::NAMESPACE_SEPARATOR, $classname);
        $path = $base . self::DIR_SEPARATOR. implode(self::DIR_SEPARATOR, $parts) . '.php';
        if (file_exists($path)) {
            require_once($path);
        }
    }

    /**
     * Initializes resources (i.e. registers them with Resource::_registry). Note
     * that if you add a Resource then you must initialize it here.
     * 
     * @internal
     */
    private static function initializeResources()
    {
        if (self::$initialized)
            return;
        
        self::$initialized = true;
    }
}
