<?php

class Crunchbutton_Upload {
	public function __construct($params = []) {
		$this->file = $params['file'];
		$this->resource = $params['resource'];
		$this->bucket = $params['bucket'];
		$this->headers = [];
		if ($params['type']) {
			$this->headers['Content-Type'] = $params['type'];
		}
	}

	public function upload() {
		$fileInfo = pathinfo($this->file);
		$fullPath = trim($fileInfo['dirname'].'/'.$fileInfo['basename']);
		$fileName = trim($fileInfo['basename']);

		$r = S3::putObject(S3::inputFile($fullPath, false), $this->bucket, $this->resource ? $this->resource : $fileName, S3::ACL_PUBLIC_READ, [], $this->headers);

		return $r == 1 ? true : false;
	}
}