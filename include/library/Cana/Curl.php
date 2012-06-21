<?php

/**
 * Baic Curl wrapper
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2006.03.05
 *
 */

class Cana_Curl extends Cana_Model {
	var $headers;
	private $_headersRaw;
	var $response;
	var $error;
	var $request;
	
	public function __construct($url, $data = null, $method = 'post', $proxy = null, $headers = false, $useragent = null) {
		$this->request($url, $data, $method, $proxy, $headers, $useragent);
		$this->request = $url;
		$this->parseHeaders($this->_headersRaw);
	}
	
	private function request($url, $data = null, $method = 'post', $proxy = null, $headers = false, $useragent = null) {

		$ch = curl_init();	
		
		$datapost = '';
		
		if ($method == 'post') {
			if (is_array($data)) {
				foreach ($data as $key => $item) {
					if ($datapost)
						$datapost .= '&';
					$datapost .= $key.'='.@urlencode($item);
				}
			}
			curl_setopt($ch, CURLOPT_URL,$url);  
			curl_setopt($ch, CURLOPT_POST, true); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $datapost);
		} else {
			if (is_array($data)) {
				foreach ($data as $key => $item) {
					$datapost .= ($datapost ? '&' : '?').$key.'='.@urlencode($item);
				}
			}
			curl_setopt($ch, CURLOPT_URL,$url.$datapost);  
			curl_setopt($ch, CURLOPT_HTTPGET, true); 
		}
		
		if (!is_null($proxy)) {
		
			//curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
			//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_PROXY, true);
			curl_setopt($ch, CURLOPT_PROXY, $proxy['server']);
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['user'].':'.$proxy['pass']);
			
		}
		//curl_setopt($ch, CURLOPT_FILE, $fp)
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		if (!is_null($useragent)) {
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		}

		
		if ($headers) {
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		
		$this->output = curl_exec($ch);
		if ($headers) {
			$sep = strpos($this->output, "\r\n\r\n") === false ? "\n\n" : "\r\n\r\n";
			list($this->_headersRaw, $this->output) = explode($sep, $this->output, 2);
		}
		
		$this->error = curl_error($ch);
		curl_close ($ch);
	}
	
	private function parseHeaders($headers) {
		$this->headers = array();
		foreach (explode("\n",$headers) as $header) {
			if (preg_match('/HTTP\//i',$header)) {
				$header = explode(' ',$header);
				$this->headers[$header[0]] = $header[1];
			} else {
				$header = explode(':',$header, 2);
				$this->headers[$header[0]] = $header[1];
			}
		}
	
	}
}