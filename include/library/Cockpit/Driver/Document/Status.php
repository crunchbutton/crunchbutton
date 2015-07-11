<?php

class Cockpit_Driver_Document_Status extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_document_status')
			->idVar('id_driver_document_status')
			->load($id);
	}

	public function www(){
		return Util::uploadWWW() . 'drivers-doc/';
	}

	public function download_url(){
		return '/api/driver/documents/download/' . $this->id_driver_document_status;
	}

	public function path(){
		return Util::uploadPath() . '/drivers-doc/';
	}

	public function doc_path(){
		return Util::uploadPath() . '/drivers-doc/' . $this->file;
	}

	public function url(){
		return $this->www() . $this->file;
	}

	public function driver_document(){
		if( !$this->_driver_document ){
			$this->_driver_document = Cockpit_Driver_Document::o( $this->id_driver_document );
		}
		return $this->_driver_document;
	}

	public function document( $id_admin, $id_driver_document ){
		$document = Cockpit_Driver_Document_Status::q( 'SELECT * FROM driver_document_status WHERE id_admin = ? AND id_driver_document = ?', [$id_admin, $id_driver_document])->get( 0 );
		if( $document->id_driver_document ){
			return $document;
		}
		return new Cockpit_Driver_Document_Status();
	}

	public function date(){
		if( !$this->_date ){
			$this->_date = new DateTime($this->datetime, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public function doc(){
		if( !$this->_doc ){
			$this->_doc = Cockpit_Driver_Document::o( $this->id_driver_document );
		}
		return $this->_doc;
	}

	public function admin_approved(){
		if( $this->id_admin_approved ){
			if( !$this->_admin_approved ){
				$this->_admin_approved = Admin::o( $this->id_admin_approved );
			}
			return $this->_admin_approved;
		}
		return false;
	}

	public function lastUpdatedDocs(){
		return Cockpit_Driver_Document_Status::q('
			SELECT dds.*,
			 IF (doc_status.completed,
					 1,
					 0) AS completed
					 FROM driver_document_status dds
					 LEFT JOIN
			(SELECT id_admin,
					IF(COUNT(*) = 3,
												1,
												0) as completed
			 FROM driver_document_status
			 WHERE id_driver_document IN(1, 2, 3)
			 GROUP BY id_admin) doc_status ON doc_status.id_admin = dds.id_admin
			ORDER BY id_admin_approved ASC,
				 completed DESC,
				 datetime DESC
		');
	}

	public function driver(){
		if( !$this->_driver ){
			$this->_driver = Admin::o( $this->id_admin );
		}
		return $this->_driver;
	}

	public function exports(){
		$out = $this->properties();
		$date = $this->date();
		$out[ 'date_formated' ] = $date->format('M jS Y g:i:s A T');
		//$out[ 'url' ] = 'https://s3.amazonaws.com/' . c::config()->s3->buckets->{'document-upload'}->name . '/' . $out[ 'file' ];
		//$out[ 'url' ] = '/' . c::config()->s3->buckets->{'document-upload'}->name . '/' . $out[ 'file' ];
		return $out;
	}


	// shouldnt need this in the future once we only allow uploads after the resource is in the db
	public static function toS3($path, $name) {

		$res = new self;
		$r = $res->localToS3($path, $name);

		return $r;
	}

	public function localToS3($path = null, $name = null) {
		if (!$path) {
			$path = $this->path().$this->file;
		}

		if (!$name) {
			$name = $this->s3Base();
		}

		if (!$path || !$name) {
			return false;
		}

		$upload = new Crunchbutton_Upload([
			'file' => $path,
			'resource' => $name,
			'bucket' => c::config()->s3->buckets->{'document-upload'}->name,
			'private' => true
		]);

		$this->file = $name;
		//$this->save();

		return $upload->upload();
	}

	public function s3Base($file = null, $name = null) {
		if (!$file) {
			$file = $this->file;
		}
		if (!$name) {
			$name = $this->name;
		}
		$pos = strrpos($file, '.');
		$ext = substr($file, $pos+1);

		$name = strtolower($name);
		$name = preg_replace('/[^a-z0-9]/i','-',$name);
		$name = preg_replace('/\-{2,}/','-', $name);
		$name = trim($name, '-');

		return $this->id_driver_document_status.'-'.$name.'.'.$ext;
	}
	
	public function getFile() {
		$file = tempnam(sys_get_temp_dir(), 'restaurant-image');
		$fp = fopen($file, 'wb');
		if (($object = S3::getObject(c::config()->s3->buckets->{'document-upload'}->name, $this->file, $fp)) !== false) {
			//var_dump($object);
		} else {
			$file = false;
		}

		return $file;
	}

}