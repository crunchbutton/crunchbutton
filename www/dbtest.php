<?php
error_reporting(E_ALL ^ ( E_NOTICE | E_STRICT | E_DEPRECATED ) );
ini_set('display_errors',true);

$getDb = function($args) {

	$args = new stdClass;
	$args->url = getenv('DATABASE_URL');

	if ($args->url) {
		preg_match('/^(mysql:\/\/)(.*):(.*)@([a-z0-9_\-\.]+)(:([0-9]+))?\/([a-z0-9\._]+)(\?sslca=(.*))?$/ui', $args->url, $matches);
		$args->user = $matches[2];
		$args->pass = $matches[3];
		$args->host = $matches[4];
		$args->port = $matches[6];
		$args->db = $matches[7];
		$args->sslca = $matches[9];
	}
	print_r($args);

	if (!$args->dsn) {
		$args->dsn = 'mysql:host='.$args->host.';dbname='.$args->db.';charset=utf8';
	}

	if ($args->persistent) {
		$options[PDO::ATTR_PERSISTENT] = true;
	}

	if ($args->sslca && !$_REQUEST['nossl']) {
		$options[PDO::MYSQL_ATTR_SSL_CA] = $args->sslca;
		$options[PDO::ATTR_TIMEOUT] = 4;
		$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
	}

	if (getenv('DEBUG')) {
		error_log('>> CONNECTING TO DATABASE...');
	}

	$db = new \PDO($args->dsn, $args->user, $args->pass, $options);

	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

	return $db;
};


$dbRead = $getDb((object)['url' => $_ENV['DATABASE_URL_READER']]);
$dbWrite = $getDb((object)['url' => $_ENV['DATABASE_URL_WRITER']]);

if ($_ENV['DEBUG']) {
	error_log('>> CONNECTED');
}


$query = 'update session set token=? where id_session=?';
$args = [rand(1,99999), '2jvvbkeh0k4bvb38or2lo4m1s7'];

$stmt = $dbWrite->prepare($query);
$stmt->execute($args);


$query = 'select * from site where active=?';
$stmt = $dbRead->prepare($query);
$stmt->execute([true]);

while ($o = $stmt->fetch()) {
	print_r($o);
}

$query = 'select * from site where active=:active';
$stmt = $dbRead->prepare($query);
$stmt->execute([active => true]);

while ($o = $stmt->fetch()) {
	print_r($o);
}


die('done');
