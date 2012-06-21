<?php

/**
 * A wrapper for creating and downloading archives
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.10.02
 *
 */
 

class Cana_Zip extends Cana_Model {
	public function __construct($params) {
		$this->setOptions($params);
	}
	
	public function setOptions($params = []) {
		if (isset($params['destination'])) {
			$this->_destination = $params['destination'];
		} elseif (!isset($this->_destination)) {
			$this->_destination = realpath(dirname(__FILE__)).'/zips/';
		}
		
		if (isset($params['name'])) {
			$this->_name = $params['name'];
		} elseif (!isset($this->_name)) {
			$this->_name = 'zip.zip';
		}
		
		if (!file_exists($this->_destination)) {
			throw new Exception('Destination directory "'.$this->_destination.'" does not exist.');
		}
	}
	
	public function create($files = [], $params = []) {
		$this->setOptions($params);

		$valid_files = [];

		if (is_array($files)) {
			foreach($files as $filename => $file) {
				if (file_exists($file)) {
					$valid_files[$filename] = $file;
				}
			}
		}
		
		$this->_realName = $this->createFileName($valid_files);
		
		if (count($valid_files)) {
		

			// ZIPARCHIVE::CREATE
			$zip = new ZipArchive();
			
			if ($zip->open($this->_realName, ZIPARCHIVE::OVERWRITE) !== true) {
				return [
					'error'		=> 'failed to open',
					'file'		=> $this->_realName
				];
			}

			foreach ($valid_files as $filename => $file) {
				$zip->addFile($file,$filename);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
			
			//close the zip -- done!
			$zip->close();
			
			//check to make sure the file exists
			return [
				'error'		=> file_exists($this->_realName) ? '' : 'failed to create',
				'file'		=> $this->_realName
			];

		} else {
			return [
				'error'		=> 'no valid files',
				'file'		=> $this->_realName
			];
		}
	}
	
	public function createFileName($files) {
		return $this->_destination.md5(print_r($files,1)).'.zip';
	}
	
	
	public function displayZip() {
		header('HTTP/1.1 200 OK');
		header('Last-Modified: '.date('r',filemtime($this->_realName)));
		header('Accept-Ranges: bytes');
		header('Content-Length: '.filesize($this->_realName));
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename='.basename($this->_name));
		readfile($this->_realName);
		exit;
	}

}