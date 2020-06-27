<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "\nInstalling...";

$url = parse_url(getenv('DATABASE_URL') ?? getenv('JAWSDB_MARIA_URL'));

if (!$url) {
	echo "\nNo DATABASE_URL or JAWSDB_MARIA_URL";
	exit(0);
}
$type = $url['scheme'] == 'postgres' ? 'pgsql' : 'mysql';
$options = null;

$db = new \PDO($type.':host='.$url['host'].($url['port'] ? ';port='.$url['port'] : '').';dbname='.substr($url['path'], 1), $url['user'], $url['pass'], $options);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

if ($argv[1] == '--force') {
	// drop all the tables and force install
	echo "\nCDroping existing tables...";

	$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = '".substr($url['path'], 1)."';";
	$exec = "SET FOREIGN_KEY_CHECKS = 0;\n";
	foreach ($db->query($query) as $row) {
		$exec .= 'DROP TABLE IF EXISTS `'.$row[0]."`;\n";
	}
	$exec .= "SET FOREIGN_KEY_CHECKS = 1;\n";
	$db->exec($exec);

	echo "complete.";
}

echo "\nCreating db schema...";
$db->exec(file_get_contents(__DIR__ . '/../db/dump.sql'));
echo "complete.\n";

echo "Running db migrate scripts...\n";

$dirs = [__DIR__ . '/../db/migrate'];

if (getenv('TRAVISPOSTGRES')) {
	echo "Running db migrate scripts for Postgres...\n";
	$dirs[] = __DIR__ . '/../db/migratepostgres';
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
],file_get_contents(__DIR__ . '/../db/dummy.sql'));

$db->exec($sql);
echo "complete.\n";

if ($error) {
	exit(1);
} else {
	exit(0);
}