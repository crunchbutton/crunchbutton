<?php
// this script will run after a successfull heroku deployment

echo "\nCreating db schema...";

$url = parse_url(getenv('JAWSDB_MARIA_URL'));
if (!$url) {
	echo "No JAWSDB_MARIA_URL";
	exit(0);
}
$type = $url['scheme'] == 'postgres' ? 'pgsql' : 'mysql';

$db = new \PDO($type.':host='.$url['host'].($url['port'] ? ';port='.$url['port'] : '').';dbname='.substr($url['path'], 1), $url['user'], $url['pass'], $options);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$db->exec(file_get_contents('db/dump.sql'));

echo "complete.\n";

echo "Running db migrate scripts...\n";

$dirs = ['db/migrate'];

if (getenv('TRAVISPOSTGRES')) {
	echo "Running db migrate scripts for Postgres...\n";
	$dirs[] = 'db/migratepostgres';
}

$error = false;

foreach ($dirs as $dir) {

	$files = [];

	foreach (new DirectoryIterator($dir) as $fileInfo) {
		if ($fileInfo->isDot()) continue;
		$num = preg_replace('/^([0-9]+)_.*$/','\\1', $fileInfo->getFilename());
		if ($num > 488) {
			$files[] = $fileInfo->getFilename();
		}
	}

	natcasesort($files);

	foreach ($files as $file) {
		echo "	".$file."...";
		$db->exec(file_get_contents($dir.'/'.$file));
		echo "complete.\n";
	}
}

echo "Inserting dummy data...";

require_once('vendor/arzynik/cana/src/Cana/Model.php');
require_once('vendor/arzynik/cana/src/Cana/Crypt.php');
$crypt = new Cana_Crypt(getenv('ENCRYPTION_KEY'));

$sql = str_replace([
	'_ADMIN_',
	'_LOGIN_',
	'_PHONE_',
	'_PASSWORD_'
],[
	getenv('ADMIN_NAME'),
	getenv('ADMIN_LOGIN'),
	getenv('ADMIN_PHONE'),
	sha1($crypt->encrypt(getenv('ADMIN_PASSWORD')))
],file_get_contents('db/dummy.sql'));

$db->exec($sql);
echo "complete.\n";

if ($error) {
	exit(1);
} else {
	exit(0);
}