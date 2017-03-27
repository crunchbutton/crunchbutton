<?php


header('Content-type: text/txt');

switch ($_SERVER['SERVER_NAME']) {
	case 'crunchbutton.com':
		echo "User-agent: *\nDisallow: /test/";
		break;
	default:
		echo "User-agent: *\nDisallow: /";
		break;
}

exit;
