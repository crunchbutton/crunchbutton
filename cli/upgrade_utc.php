#!/usr/bin/env php
<?php

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);

if (trim(`whoami`) == 'arzynik') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
}

echo "\x1B[44mStarging conversion...\x1B[0m\n";

require_once '../include/crunchbutton.php';

$t = c::db()->query('show tables');
$ignore = ['session', 'log', 'analytics_event', 'admin_location', 'MergeCommunity', 'community_shift'];
$completed = file('/tmp/completed-utc-conversion');
foreach ($completed as $k => $complete) {
	$completed[$k] = trim($complete);
}

foreach ($t as $table) {
	if (in_array($table->Tables_in_crunchbutton, $ignore)) {
		continue;
	}
	$tables[] = $table->Tables_in_crunchbutton;
}

foreach ($tables as $table) {
	$c = c::db()->query('show columns from `'.$table.'`');
	foreach ($c as $column) {
		if ($column->Type == 'datetime') {
			echo '  '.$table.'.'.$column->Field.'...';
			if (in_array($table.'.'.$column->Field, $completed)) {
				echo "\x1B[31mskipping.\x1B[0m\n";
				continue;
			}

			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 8 hour)
				where `'.$column->Field.'` >= "2015-11-01 02:00:00"
				and `'.$column->Field.'` < "2016-01-07 10:00:00"
			';
			c::db()->exec($query);
			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 7 hour)
				where `'.$column->Field.'` < "2015-11-01 02:00:00"
				and  `'.$column->Field.'` >= "2015-03-08 02:00:00"
			';
			c::db()->exec($query);
			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 8 hour)
				where `'.$column->Field.'` < "2015-03-08 02:00:00"
				and  `'.$column->Field.'` >= "2014-11-02 02:00:00"
			';
			c::db()->exec($query);
			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 7 hour)
				where `'.$column->Field.'` < "2014-11-02 02:00:00"
				and  `'.$column->Field.'` >= "2014-03-02 02:00:00"
			';
			c::db()->exec($query);
			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 8 hour)
				where `'.$column->Field.'` < "2014-03-02 02:00:00"
				and  `'.$column->Field.'` >= "2013-11-03 02:00:00"
			';
			c::db()->exec($query);
			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 7 hour)
				where `'.$column->Field.'` < "2013-11-03 02:00:00"
				and  `'.$column->Field.'` >= "2013-03-10 02:00:00"
			';
			c::db()->exec($query);
			$query = '
				update `'.$table.'` set `'.$column->Field.'` = date_add(`'.$column->Field.'`, INTERVAL 8 hour)
				where `'.$column->Field.'` < "2013-03-10 02:00:00"
			';
			c::db()->exec($query);

			echo "\x1B[32msuccess\x1B[0m\n";
			exec ('echo '.$table.'.'.$column->Field.' >> /tmp/completed-utc-conversion');
		}
	}
}

echo "\nConversion complete.\n\n";
