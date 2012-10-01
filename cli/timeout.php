#!/usr/local/bin/php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(10);

$a = (object)getopt('s::c::r::f::');

if ($a->s) {
	sleep($a->s / 1000); // ms
}

// include our libraries AFTER the nap, so we dont keep mysql or our memory awake
require_once('../include/crunchbutton.php');

if ($a->r) {
	eval($a->r);
}

if ($a->c) {
	$c = unserialize(base64_decode($a->c));
	$c->__invoke();
}

if ($a->f) {
	require_once('./'.$a->f);
}