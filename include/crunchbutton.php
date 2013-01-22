<?php

/**
 * Bootloader
 *
 * @author    Devin Smith <devin@cana.la>
 * @date     2009.09.17
 *
 * The bootloader to include the luma application class
 *
 */

// keep the directory setup in here so we can change its path later

set_include_path(get_include_path() . PATH_SEPARATOR . '/Users/arzynik/pear/share/pear');

$GLOBALS['config'] = [
	'dirs' => [
		'controller'		=> dirname(__FILE__).'/controllers/',
		'cache'				=> dirname(__FILE__).'/../cache/',
		'pubcache'			=> dirname(__FILE__).'/../www/cache/',
		'config'			=> dirname(__FILE__).'/config/',
		'view'				=> dirname(__FILE__).'/views/',
		'library'			=> dirname(__FILE__).'/library/',
		'root'				=> dirname(__FILE__).'/../',
		'www'				=> dirname(__FILE__).'/../www/',
		'storage'			=> dirname(__FILE__).'/../storage/',
	],'libraries' 			=> ['Crunchbutton','Cana','Services','Balanced','Ordrin'],
	'alias'					=> []
];


if (!isset($_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = '';
}

$_SERVER['__HTTP_HOST'] = $_SERVER['HTTP_HOST'];


spl_autoload_register(function ($className) {

	foreach ($GLOBALS['config']['alias'] as $k => $v) {
		if ($className == $k) {
			$className = $v;
			$setAlias = function($v, $k) {
				class_alias($v, $k);
			};
			break;
		}
	}

	if (strpos($className, '\\') !== false) {

		$classes = explode('\\', $className);
		$dir = array_shift($classes);
		$classes = implode('\\', $classes);

		$className = str_replace('\\','/',$classes);

		$libraries = [$dir];
		$ignoreAlias = true;

	} else {
		$libraries = $GLOBALS['config']['libraries'];
	}

	$class = str_replace('_','/',$className);

	if (file_exists($GLOBALS['config']['dirs']['library'] . $class . '.php')) {
		require_once $GLOBALS['config']['dirs']['library'] . $class . '.php';
		if ($setAlias) $setAlias($v, $k);
		return;
	}

	foreach ($libraries as $prefix) {
		$fileName = $GLOBALS['config']['dirs']['library'] . $prefix . '/' . $class . '.php';

		if (file_exists($fileName)) {
			require_once $fileName;
			if (!$ignoreAlias) {
				class_alias($prefix.'_'.$className, $className);
			}
			return;
		}
	}

	throw new Cana_Exception_MissingLibrary('The file '.$GLOBALS['config']['dirs']['library'] . $className . '.php'.' does not exist');
	exit;
});


\Httpful\Bootstrap::init();
\Balanced\Bootstrap::init();
\Ordrin\Bootstrap::init();

$configFile = $GLOBALS['config']['dirs']['config'].'config.demo.xml';
if (file_exists($GLOBALS['config']['dirs']['config'].'config.xml')) {
	$configFile = $GLOBALS['config']['dirs']['config'].'config.xml';
}


// init (construct) the static Caffeine application and display the page requested
Cana::init([
	'app' => 'Crunchbutton_App',
	'config' => (new Cana_Config($configFile))->merge($GLOBALS['config'])
]);

