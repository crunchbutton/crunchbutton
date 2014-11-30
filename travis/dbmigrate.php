<?php
	
$files = [];

foreach (new DirectoryIterator('db/migrate') as $fileInfo) {
	if ($fileInfo->isDot()) continue;
	$num = preg_replace('/^([0-9]+)_.*$/','\\1', $fileInfo->getFilename());
	if ($num > 180) {
		$files[] = $fileInfo->getFilename();
	}
}

natcasesort($files);

echo "\nRunning db migrate scripts...\n";

foreach ($files as $file) {
	echo $file."...\n";
	$o = null;

	exec('mysql -uroot crunchbutton_travis < db/migrate/'.$file.' 2>&1 &', $o);
	$ret = implode("\n", $o);

	if (preg_match('/ERROR [0-9]+/', $ret)) {
		$error = $ret;
		break;
	}
	echo "\n";
}

if ($error) {
	exit($error);
} else {
	exit(0);
}