<?php


header('Content-type: text/txt');

switch ($_SERVER['SERVER_NAME']) {
	case '_DOMAIN_':
		echo "User-agent: *\nDisallow: ";
		break;
	default:
		echo "User-agent: *\nDisallow: /";
		break;
}

exit;
