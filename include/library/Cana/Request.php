<?php

/**
 * A markdown parser wrapper
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2006.03.05
 *
 */

class Cana_Request extends Cana_Model {
	var $headers;
	private $_headersRaw;
	var $response;
	var $error;
	var $request;
	
	public function __construct($url, $data = null, $method = 'post', $proxy = null, $headers = false, $useragent = null, $savepath = null) {
		$this->request($url, $data, $method, $proxy, $headers, $useragent, $savepath);
		$this->request = $url;
		$this->parseHeaders($this->_headersRaw);
	}
	
	private function request($url, $data = null, $method = 'post', $proxy = null, $headers = false, $useragent = null, $savepath = null) {
		
		$datapost = '';
		if (is_array($data)) {
			foreach ($data as $key => $item) {
				$datapost .= ($datapost ? '&' : '?').$key.'='.@urlencode($item);
			}
		}
		
		$options = [
			'http' => [
				'method'		=> (strtolower($method) == 'post' ? 'POST' : 'GET'),
				'user_agent'	=> (is_null($useragent) ? 'PHP/Cana' : $useragent)
			]
		];
		stream_context_get_default($options);
		
		$this->r = fopen($url.$datapost, 'r');
		
		if (!is_null($savepath)) {
			$this->write($savepath);
		} elseif ($savepath === false) {
			$this->read();
		} else {
			$this->output = null;
		}

		if ($headers) {
			$this->_headersRaw = stream_get_meta_data($this->r);
			$this->_headersRaw = $this->_headersRaw['wrapper_data'];
		}
	}
	
	public function read() {
		$this->output = '';
		while (!feof($this->r)) {
		    $this->output .= fread($this->r, 8192);
		}
		fclose($this->r);
	}
	
	public function write($savepath) {
		$this->output = null;
		$w = fopen($savepath, 'w');
		while (!feof($this->r)) {
		    $output = fread($this->r, 8192);
		    fwrite($w, $output);
		}
		fclose($this->r);
	}
	
	private function parseHeaders($headers) {
		$this->headers = array();
		foreach ($headers as $header) {
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