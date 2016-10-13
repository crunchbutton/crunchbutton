#!/usr/bin/env php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(100);

$_REQUEST['__host'] = $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = '_DOMAIN_';
$_REQUEST['__url'] = '';

echo "\n\x1B[44mUploading build files to aws...\x1B[0m\n";

require_once '../include/crunchbutton.php';

//$git = Cana_Util::gitVersion();
$git = getenv('HEROKU_SLUG_COMMIT');

if (!$git) {
	$git = trim(shell_exec('cd '.Cana::config()->dirs->root.' && git rev-parse HEAD'));
}

if (!$git) {
	echo "\x1B[31mFailed to get git version.\x1B[0m\n";
	exit(1);
} else {
	echo "Found git version $git\n";
}

$v = new Crunchbutton_Deploy_Version([
	'id_deploy_server' => 23,
	'date' => date('Y-m-d H:i:s'),
	'status' => 'new',
	'version' => $git
]);
//$v->save();
//$build = $v->id_deploy_version;
$build = $git;

$files = [
	Cana::config()->dirs->www.'assets/css/bundle.css' => 'css/crunchbutton.css',
	Cana::config()->dirs->www.'assets/cockpit/css/bundle.css' => 'css/cockpit.css',
	Cana::config()->dirs->www.'assets/js/bundle.js' => 'js/crunchbutton.js',
	Cana::config()->dirs->www.'assets/cockpit/js/bundle.js' => 'js/cockpit.js'
];

$path = c::config()->dirs->www.'assets/images';
$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
$fs = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);

foreach ($fs as $fileInfo) {
	if ($fileInfo->getBasename() == '.DS_Store') {
		continue;
	}
	$p = str_replace($path,'',$fileInfo->getPath());
	if ($fileInfo->isFile() && ((!$p && substr($fileInfo->getBasename(),0,1) != '.') || ($p && !in_array($p, $exclude)))) {
		$name = str_replace('//','/','images/'.$p.'/'.$fileInfo->getBasename());
		$ph = $path.$p.'/'.$fileInfo->getBasename();
		$files[$ph] = $name;
	}
}
$finfo = finfo_open(FILEINFO_MIME_TYPE);
foreach ($files as $src => $dst) {
	echo "	Uploading $dst...";
	$type = finfo_file($finfo, $src);

	$upload = new Crunchbutton_Upload([
		'file' => $src,
		'resource' => $build.'/'.$dst,
		'bucket' => c::config()->s3->buckets->build->name,
		'private' => false,
		'type' => $type
	]);
	$s = $upload->upload();

	echo ($s ? "\x1B[32msuccess\x1B[0m" : "\x1B[31mfailed\x1B[0m") . ".\n";
}

echo "Completed build ".$git.".\n\n";

//$v->status = 'success';
//$v->save();


exit(0);
