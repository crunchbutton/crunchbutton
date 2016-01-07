#!/usr/bin/env php
<?php
//local

$file = '../db/dump.sql';

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

if (trim(`whoami`) == 'arzynik') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
	$dump = '/Applications/MAMP/Library/bin/mysqldump';
	$mysql = '/Applications/MAMP/Library/bin/mysql';
} else {
	$dump = 'mysqldump';
	$mysql = 'mysql';
}

require_once('../include/crunchbutton.php');


$remote = c::config()->db->live;
$local = c::config()->db->local;

$schemaOnly = ['session','log','analytics_event','admin_location'];
foreach ($schemaOnly as $t) {
	$ignoreTables .= ' --ignore-table=crunchbutton.'.$t.' ';
}

// delete existing file
$cmd[] = 'rm '.$file;

// schema
$cmd[] = $dump.' -d --lock-tables=false -h _HOST_ -u '.c::crypt()->decrypt($remote->user).' -p'.c::crypt()->decrypt($remote->pass).' '.$remote->db.' >> '.$file;

// data
$cmd[] = $dump.' --no-create-info --skip-triggers --lock-tables=false '.$ignoreTables.' -h _HOST_ -u '.c::crypt()->decrypt($remote->user).' -p'.c::crypt()->decrypt($remote->pass).' '.$remote->db.' >> '.$file;

//$cmd[] = $dump.' --no-create-info --skip-triggers -u '.$connect->user.' -p'.$connect->pass.' '.$connect->db.' config group site >> '.$file;
// replace trigger creators
$cmd[] = 'sed  "s/\`devin\`@\`%\`/\`root\`@\`localhost\`/g" '.$file.' > '.$file.'tmp';
$cmd[] = 'mv '.$file.'tmp '.$file;

$cmd[] = 'sed  "s/\`crunchapple\`@\`%\`/\`root\`@\`localhost\`/g" '.$file.' > '.$file.'tmp';
$cmd[] = 'mv '.$file.'tmp '.$file;

// import into local db
$cmd[] = $mysql.' -u '.$local->user.' -p'.$local->pass.' '.$local->db.' < '.$file;

// import into postgres
/*
$cmd[] = 'pgloader mysql://'.$local->user.':'.$local->pass.'@localhost:8889/'.$local->db.' postgresql:///metrics';

// export postgres dump
//PGPASSWORD=mypassword
$cmd[] = 'pg_dump -Fc --no-acl --no-owner -h localhost metrics -f crunchbutton.psql';

// delete existing heroku db
$cmd[] = 'heroku pg:reset --confirm crunchbutton HEROKU_POSTGRESQL_NAVY';

// import the uploaded dump into heroku
$cmd[] = "heroku pg:backups restore --confirm crunchbutton 'http://drop.crunchr.co/crunchbutton.psql' HEROKU_POSTGRESQL_NAVY";
*/

foreach ($cmd as $c) {
	echo $c."\n";
	exec($c);
}

