#!/usr/bin/env php
<?php

error_reporting(E_ALL ^ ( E_NOTICE | E_STRICT | E_DEPRECATED ) );

// create static assets

$host = $argv[1];
$uri = $argv[2];
$params = $argv[3];

parse_str($params, $_GET);
parse_str($params, $_REQUEST);

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);
$_REQUEST['__host'] = $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $host;
$_REQUEST['__url'] = $uri;
require_once '../include/crunchbutton.php';
Cana::app()->displayPage();
