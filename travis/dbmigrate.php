<?php


echo "\nRunning db migrate scripts...\n";

$dirs = ['db/migrate'];

if ($_ENV['TRAVISPOSTGRES']) {
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
		echo $file."...\n";
		$o = null;

		exec('mysql -u root crunchbutton_travis < '.$dir.'/'.$file.' 2>&1 &', $o);
		$ret = implode("\n", $o);

		if (preg_match('/ERROR [0-9]+/', $ret)) {
			$error = true;
			echo "\033[31m";
			echo $ret."\n";
			echo "\033[37m";
			break;
		}
	}
}

if ($error) {
	exit(1);
} else {
	exit(0);
}
