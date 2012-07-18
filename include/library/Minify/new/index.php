<?php
/**
 * Front controller for default Minify implementation
 * 
 * DO NOT EDIT! Configure this utility via config.php and groupsConfig.php
 * 
 * @package Minify
 */

define('MINIFY_MIN_DIR', dirname(__FILE__));



ini_set('zlib.output_compression', '0');


// setup include path
set_include_path(dirname(__FILE__) . '/lib' . PATH_SEPARATOR . get_include_path());

require 'Minify.php';

Minify::setCache(dirname(__FILE__).'/../../../cache/min/');

Minify::serve('Files', [
	'files'  => ['/Users/arzynik/Sites/crunchbutton/www/assets/min/quick-test.js'],
	'maxAge' => 86400]
);