<?php

class Crunchbutton_Community_Resource extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_resource')
			->idVar('id_community_resource')
			->load($id);
	}
	public function www(){
		return Util::uploadWWW() . 'resource/';
	}

	public function download_url(){
		return '/api/community/resource/download/' . $this->id_community_resource;
	}

	public function path(){
		return Util::uploadPath() . '/resource/';
	}

	public function doc_path(){
		return Util::uploadPath() . '/resource/' . $this->file;
	}

	public function url(){
		return $this->www() . $this->file;
	}

	public function date(){
		if( !$this->_date ){
			$this->_date = new DateTime($this->datetime, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public function communities(){
		return Crunchbutton_Community_Resource_Community::q( 'SELECT * FROM community_resource_community WHERE id_community_resource = "' . $this->id_community_resource . '"' );
	}

	public function exports(){
		$out = $this->properties();
		$communities = $this->communities();
		if( $communities ){
			$out[ 'communities' ] = [];
			foreach( $communities as $community ){
				$out[ 'communities' ][] = $community->id_community;
			}
		}

		return $out;
	}

	public function admin(){
		if( $this->id_admin ){
			if( !$this->_admin ){
				$this->_admin = Admin::o( $this->id_admin );
			}
			return $this->_admin;
		}
		return false;
	}
}