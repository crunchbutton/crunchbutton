#!/usr/bin/env php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(100);

echo "\x1B[44mRunning Cron...\x1B[0m\n";

require_once('../include/crunchbutton.php');
Crunchbutton_Cron_Log::start();
