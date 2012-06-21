<?php

/**
 * A resumable download and force download file class
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2010.06.03
 *
 */

class Cana_File extends Cana_Model {

	private $_file;

	public function __construct($params) {
		$this->_file = self::cleanFileName($params['file']);
		$this->_path = $params['path'];
		$this->_name = $params['name'] ? $params['name'] : $params['file'];
	}
	

	public function get($attachment = false, $resume = true, $filename = null) {
	
		$file = $this->_path.$this->_file;
		$filename = $filename ? $filename : $this->_name;

		if (!file_exists($file)) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		
		if ($attachment) {
			if (ini_get('zlib.output_compression')) {
				ini_set('zlib.output_compression', 'Off');
			}
		}

		$size = filesize($file);
		$fileinfo = pathinfo($file);
   
		//workaround for IE filename bug with multiple periods / multiple dots in filename
		//that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
		if (is_null($filename)) {
			$filename = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
	    		preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
	   			$fileinfo['basename'];
		}

  		//$file_extension = strtolower($fileinfo['extension']);

		if ($resume && isset($_SERVER['HTTP_RANGE'])) {
			list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

			if ($size_unit == 'bytes') {
				list($range, $extra_ranges) = explode(',', $range_orig, 2);
			} else {
				$range = '';
			}
		} else {
			$range = '';
		}
		
		if ($range) {
			list ($seek_start, $seek_end) = explode('-', $range, 2);
		} else {
			$seek_start = '';
			$seek_end = '';
		}


		$seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
		$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);


		if ($resume) {
			if ($seek_start > 0 || $seek_end < ($size - 1)) {
				header('HTTP/1.1 206 Partial Content');
			} else {
				header('HTTP/1.1 200 OK');
			}

			header('Accept-Ranges: bytes');
			header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
		} else {
			header('HTTP/1.1 200 OK');
			header('Accept-Ranges: bytes');
		}

		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Date: '.date('r'));
		header('Last-Modified: '.date('r',filemtime($file)));
		header('Content-Length: '.($seek_end - $seek_start + 1));
		header('Content-Transfer-Encoding: binary');
		
		if ($attachment) {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Content-Type: application/force-download');
		} else {
			header('Content-Type: '. mime_content_type($file));
		}

		$fp = fopen($file, 'rb');
		fseek($fp, $seek_start);

		while(!feof($fp)) {
			set_time_limit(0);
			print(fread($fp, 1024*8));
			flush();
		}

		fclose($fp);
	}
	
	
	public static function cleanFileName($file) {
		$find = ['/\\\/','/\.\.\//'];
		$replace = ['/',''];
		$file = preg_replace($find,$replace,$file);
		return $file;
	}
}