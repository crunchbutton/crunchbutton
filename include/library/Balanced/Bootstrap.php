<?php

namespace Balanced;

/**
 * Bootstrapper for Balanced does autoloading and resource initialization.
 */
class Bootstrap
{
    const DIR_SEPARATOR = DIRECTORY_SEPARATOR;
    const NAMESPACE_SEPARATOR = '\\';

    public static $initialized = false;
    
    
    public static function init()
    {
        spl_autoload_register(array('\Balanced\Bootstrap', 'autoload'));
        self::initializeResources();
    }
    
    public static function autoload($classname)
    {
        self::_autoload(dirname(dirname(__FILE__)), $classname);
    }
    
    public static function pharInit()
    {
        spl_autoload_register(array('\Balanced\Bootstrap', 'pharAutoload'));
        self::initializeResources();
    }
    
    public static function pharAutoload($classname)
    {
        self::_autoload('phar://balanced.phar', $classname);
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
         
        \Balanced\Core\Resource::init();
        \Balanced\APIKey::init();
        \Balanced\Marketplace::init();
        \Balanced\Account::init();
        \Balanced\Credit::init();
        \Balanced\Debit::init();
        \Balanced\Refund::init();
        \Balanced\Card::init();
        \Balanced\BankAccount::init();
        \Balanced\Hold::init();
        \Balanced\Merchant::init();
        
        self::$initialized = true;
    }
}
