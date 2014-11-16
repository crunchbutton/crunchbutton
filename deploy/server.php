<?php

include(dirname(__FILE__).'/gitupdate.php');

echo "\nRestarting services...\n";

$o = null;
exec('service httpd restart 2>&1 &', $o);
echo implode("\n", $o)."\n";
