<?php

class Cockpit_Fax extends Cana_Table {

	const STATUS_NEW = 'new';
	const STATUS_SENDING = 'sending';
	const STATUS_ERROR = 'error';
	const STATUS_SUCCESS = 'success';

	const DOCUMENTS_BUCKECT = 'fax-files';

	public function __construct($id = null) {
		parent::__construct();
		$this	->table('fax')->idVar('id_fax')->load($id);
	}

	public static function upload($file, $ext){
		$bucket = c::config()->s3->buckets->{self::DOCUMENTS_BUCKECT}->name;
		if (file_exists($file)) {
			if(!$ext){
				$info = pathinfo($file);
				$ext = $info['extension'];
			}
			if (!$ext) {
				$pos = strrpos($name, '.');
				$ext = substr($name, $pos+1);
			}
			$ext = strtolower($ext);
			$name = uniqid().'.'.$ext;
			// upload the source image
			$upload = new Crunchbutton_Upload([
				'file' => $file,
				'resource' => $name,
				'bucket' => $bucket
			]);
			$result = $upload->upload();
			if($result){
				return $name;
			}
		}
		return null;
	}

	public static function create($params){
		$fax = new Cockpit_Fax;
		$fax->id_admin = c::user()->id_admin;
		$fax->date = date('Y-m-d H:i:s');
		$fax->status = self::STATUS_NEW;
		$fax->fax = Phone::clean($params['fax']);
		$fax->id_restaurant = $params['id_restaurant'];
		$fax->file = $params['file'];
		$fax->save();
		return $fax;
	}

	public function send(){
		if($this->status != self::STATUS_NEW){
			return;
		}
		if(!$this->file){
			$this->status = self::STATUS_ERROR;
			$this->message = 'Missing file!';
			$this->save();
			return;
		}
		if(!$this->fax){
			$this->status = self::STATUS_ERROR;
			$this->message = 'Missing fax number!';
			$this->save();
			return;
		}

		$file = Crunchbutton_Upload::download(c::config()->s3->buckets->{self::DOCUMENTS_BUCKECT}->name, $this->file);

		if($file){
			$ext = explode('.',$this->file);
			$ext = array_pop($ext);
			rename($file, $file.'.'.$ext);
			$file = $file.'.'.$ext;
			$fax = new Phaxio(['to' => $this->fax, 'file' => $file]);
			if($fax->success){
				$this->phaxio = $fax->faxId;
				$this->message = $fax->message;
				$this->status = self::STATUS_SUCCESS;
				$this->save();
			} else {
				$this->message = $fax->message;
				$this->status = self::STATUS_ERROR;
				$this->save();
			}
			return ['status' => $this->status, 'message' => $this->message];
		} else {
			$this->status = self::STATUS_ERROR;
			$this->message = 'Missing file!';
			$this->save();
			return;
		}
	}
}