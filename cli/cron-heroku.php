<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(100);

require_once('../include/crunchbutton.php');

mail('_EMAIL', 'TEST','asd');