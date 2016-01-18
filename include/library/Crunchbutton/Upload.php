<?php

class Crunchbutton_Upload {
	public function __construct($params = []) {
		$this->file = $params['file'];
		$this->resource = $params['resource'];
		$this->bucket = $params['bucket'];
		$this->headers = [];
		$this->permissions = $params['private'] ? 'private' : 'public-read';

		if ($params['type']) {
			$this->headers['Content-Type'] = $params['type'];
		}
	}

	public static function download( $bucket, $key ){

		$file = tempnam(sys_get_temp_dir(), 'restaurant-image');
		$fp = fopen($file, 'wb');

		try {
			$object = c::s3()->getObject([
				'Bucket' => $bucket,
				'Key'    => $key,
				'SaveAs' => $fp
			]);
			$status = true;
		} catch (Aws\Exception\S3Exception $e) {
			$status = false;
		}

		return $file;

	}

	public function upload() {
		$fileInfo = pathinfo($this->file);
		$fullPath = trim($fileInfo['dirname'].'/'.$fileInfo['basename']);
		$fileName = trim($fileInfo['basename']);
		$body = fopen($fullPath, 'r');

		//$r = S3::putObject(S3::inputFile($fullPath, false), $this->bucket, $this->resource ? $this->resource : $fileName, $this->permissions, [], $this->headers);

		try {
			c::s3()->putObject([
				'Bucket' => $this->bucket,
				'Key'    => $this->resource ? $this->resource : $fileName,
				'Body'   => $body ? $body : '',
				'ACL'    => $this->permissions,
			]);
			$status = true;
		} catch (Aws\Exception\S3Exception $e) {
			$status = false;
		}

		return $status;
	}
}
