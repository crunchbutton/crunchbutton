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
$git = trim(shell_exec('cd '.Cana::config()->dirs->root.' && git rev-parse HEAD'));

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
$v->save();
//$build = $v->id_deploy_version;
$build = $git;

$files = [
	'/app/www/assets/css/bundle.css' => 'crunchbutton.'.$build.'.css',
	'/app/www/assets/cockpit/css/bundle.css' => 'cockpit.'.$build.'.css',
	'/app/www/assets/js/bundle.js' => 'crunchbutton.'.$build.'.js',
	'/app/www/assets/cockpit/js/bundle.js' => 'cockpit.'.$build.'.js'
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
		$files[$name] = $name;
	}
}


foreach ($files as $src => $dst) {
	echo "	Uploading $dst...";
	$upload = new Crunchbutton_Upload([
		'file' => $src,
		'resource' => $dst,
		'bucket' => c::config()->s3->buckets->build->name,
		'private' => false
	]);
	$s = $upload->upload();
	if (!$s) {
		$folder = new Crunchbutton_Upload([
			'file' => '',
			'resource' => dirname($dst).'_$folder$',
			'bucket' => c::config()->s3->buckets->build->name,
			'private' => false
		]);
		$s2 = $folder->upload();
		echo ($s ? "\x1B[32mfolder created. \x1B[0m\n" : "\x1B[31mfailed to create folder. \x1B[0m");
	}
	echo ($s ? "\x1B[32msuccess\x1B[0m\n" : "\x1B[31mfailed\x1B[0m") . ".\n";
}

echo "Complete.\n\n";

$v->status = 'success';
$v->save();


exit(0);
