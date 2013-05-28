<?php 

/**
 * Cana application class
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.09.17
 * 
 *
 * This is the main application that is called by all controllers. The Cana class
 * is accesed staticly and used as a global application object. Upon a request, this
 * class routes the request to the proper controler file and class. The controllers
 * create and access the object models, populate them with data, and hand them off to
 * the Cana::view() object. This object is accessable via $this from within the
 * view phtmls.
 *
 */

class Cana extends Cana_Model {
	
	/**
	 * Private variables all have public accessor methods
	 */
	private static $_app;

	/**
	 * Initialize the Cana object
	 *
	 * @param	array		An optional config array
	 */
	public static function init($params = []) {

		if (isset($params['app'])) {
			self::app(new $params['app']($params));
		} else {
			self::app(new Cana_App($params));
		}
		self::app()->init($params);
	}
		

	/**
	 * Exception handler
	 *
	 * @param	Exception		the exception
	 */
	public static function exception($exception) {
		if (!isset(self::view()->thisPage)) {
			self::view()->thisPage = 'Exception';
		}
		if (!isset(self::view()->headers->title)) {
			self::view()->headers->title = 'Exception';
		}
		self::view()->exception = $exception;
		self::app()->displayPage('error');
	}
	
	
	/**
	 * Error handler
	 *
	 * @param	string		error number
	 * @param	string		the error text
	 * @param	string		the filename of the error
	 * @param	string		the line number the error was on
	 */
	public static function error($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
		    case E_USER_ERROR:
		    	throw new Exception('<b>['.$errno.']</b> '.$errstr.'<br />Fatal error on line '.$errline.' in file '.$errfile);
		        break;
		
		    case E_USER_WARNING:
		        throw new Exception('<b>['.$errno.'] '.$errstr.'<br />');
		        break;
		
		    case E_USER_NOTICE:
		    default:
		    	echo '<b>['.$errno.']</b> '.$errstr.'<br />Notice on line '.$errline.' in file '.$errfile.'<br />';
		    	break;
	    }
	    return true;
	}
	
	/**
	 * Accessor methods
	 */
	public static function db() {
		return self::app()->db();
	}
	
	public static function dbWrite() {
		return self::app()->dbWrite();
	}
	
	public static function auth() {
		return self::app()->auth();
	}
	
	public static function acl() {
		return self::app()->acl();
	}
	
	public static function view() {
		return self::app()->view();
	}
	
	public static function config() {
		return self::app()->config();
	} 
	
	public static function constant() {
		return self::app()->constant();
	}
	
	public static function env() {
		return self::app()->env();
	}
	
	public static function browser() {
		return self::app()->browser();
	}
	
	public static function app($app = null) {
		if (is_null($app)) {
			return self::$_app;
		} else {
			self::$_app = $app;
		}
	}
	
	public static function getPagePiece($piece = 0) {
		$pages = self::app()->pages();
		return isset($pages[$piece]) ? $pages[$piece] : null;
	}

	public static function factory($a = null, $b = null) {
		return self::app()->factory($a, $b);
	}
	
	public static function factoryCount() {
		return self::app()->factoryCount();
	}

	public static function extend() {
		return (new ReflectionMethod(self::app(), 'extend'))->invokeArgs(self::app(), func_get_args());
	}
	
	public function timeout($func, $ms = null, $async = true) {
		$closure = new SuperClosure($func);
		$encoded = base64_encode(serialize($closure));
		
		if ($ms) {
			$sleep = ' -s='.$ms;
		}

		$cmd = c::config()->dirs->root.'cli/timeout.php'.$sleep.' -c='.str_replace("'",'"',escapeshellarg($encoded));

		if ($async) {
			exec('nohup '.$cmd.' > /dev/null 2>&1 &');
		} else {
			exec($cmd, $o);
			print_r($o);
		}
	}
	
	public function __call($name, $arguments) {
		return (new ReflectionMethod(self::app(), $name))->invokeArgs(self::app(), $arguments);
	}
	
	public static function __callStatic($name, $arguments) {
		return (new ReflectionMethod(self::app(), $name))->invokeArgs(self::app(), $arguments);
	}

}

// alias the core objects for shorthand
class_alias('Cana','c');
class_alias('Cana_Iterator','i');