#!/usr/bin/env php
<?php

if (PHP_SAPI == 'cli' && isset($argv[2]) && isset($argv[3])) {

    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', true);
    set_time_limit(0);
    require_once('../include/crunchbutton.php');
    $community = Community::o($argv[2]);
    $name = $community->name;
    $community->delivery_logistics = $argv[3];
    $community->save();
}
