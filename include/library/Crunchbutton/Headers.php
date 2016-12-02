<?php
// Drop-in replacement for apache_request_headers() when it's not available

class Crunchbutton_Headers{}

if(!function_exists('apache_request_headers')) {
	function apache_request_headers() {

		// Based on: http://www.iana.org/assignments/message-headers/message-headers.xml#perm-headers
		$arrCasedHeaders = array(
			// HTTP
			'Dasl'             => 'DASL',
			'Dav'              => 'DAV',
			'Etag'             => 'ETag',
			'Mime-Version'     => 'MIME-Version',
			'Slug'             => 'SLUG',
			'Te'               => 'TE',
			'Www-Authenticate' => 'WWW-Authenticate',
			// MIME
			'Content-Md5'      => 'Content-MD5',
			'Content-Id'       => 'Content-ID',
			'Content-Features' => 'Content-features',
		);
		$arrHttpHeaders = array();

		foreach($_SERVER as $strKey => $mixValue) {
			if('HTTP_' !== substr($strKey, 0, 5)) {
				continue;
			}

			$strHeaderKey = strtolower(substr($strKey, 5));

			if(0 < substr_count($strHeaderKey, '_')) {
				$arrHeaderKey = explode('_', $strHeaderKey);
				$arrHeaderKey = array_map('ucfirst', $arrHeaderKey);
				$strHeaderKey = implode('-', $arrHeaderKey);
			}
			else {
				$strHeaderKey = ucfirst($strHeaderKey);
			}

			if(array_key_exists($strHeaderKey, $arrCasedHeaders)) {
				$strHeaderKey = $arrCasedHeaders[$strHeaderKey];
			}

			$arrHttpHeaders[$strHeaderKey] = $mixValue;
		}

		return $arrHttpHeaders;

	}
}


if (!function_exists('getallheaders')) {
	function getallheaders() {
		$headers = '';
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				@$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}