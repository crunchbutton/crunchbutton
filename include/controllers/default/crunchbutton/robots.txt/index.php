<?php


header('Content-type: text/txt');

switch ($_SERVER['SERVER_NAME']) {
	case '_DOMAIN_':
		echo "User-agent: *\nDisallow: /test/ ";
		break;
	default:
		echo "User-agent: *\nDisallow: /test/";
		break;
}

exit;
