<?php
	
$files = [];

foreach (new DirectoryIterator('db/migrate') as $fileInfo) {
	if ($fileInfo->isDot()) continue;
	$num = preg_replace('/^([0-9]+)_.*$/','\\1', $fileInfo->getFilename());
	if ($num > 180) {
		$files[] = $fileInfo->getFilename();
	}
}

foreach ($files as $file) {
	exec('mysql -uroot crunchbutton_travis < db/migrate/'.$file);
}