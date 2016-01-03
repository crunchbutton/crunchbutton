#!/usr/bin/env php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(100);

$_REQUEST['__host'] = $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = '_DOMAIN_';
$_REQUEST['__url'] = '';

echo "\n\x1B[44mUploading build files to aws...\x1B[0m\n";

require_once '../include/crunchbutton.php';

$git = Cana_Util::gitVersion();

if (!$git) {
	echo "\x1B[31mFailed to get git version.\x1B[0m\n";
	exit(1);
} else {
	echo "Found git version $git\n";
}

$v = new Crunchbutton_Deploy_Version([
	'id_deploy_server' => 23,
	'date' => date('Y-m-d H:i:s'),
	'status' => 'success',
	'version' => $git
]);
$v->save();
$build = $v->id_deploy_version;

$files = [
	'/app/www/assets/css/bundle.css' => 'crunchbutton.'.$build.'.css',
	'/app/www/assets/cockpit/css/bundle.css' => 'cockpit.'.$build.'.css',
	'/app/www/assets/js/bundle.js' => 'crunchbutton.'.$build.'.js',
	'/app/www/assets/cockpit/js/bundle.js' => 'cockpit.'.$build.'.js'
];

foreach ($files as $src => $dst) {
	echo "	Uploading $dst...";
	$upload = new Crunchbutton_Upload([
		'file' => $src,
		'resource' => $dst,
		'bucket' => c::config()->s3->buckets->build->name,
		'private' => false
	]);
	$upload->upload();
	echo "complete.\n";
}

echo "Finished.\n\n";


exit(0);
