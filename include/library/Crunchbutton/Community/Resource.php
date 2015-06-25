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
	
	public function localToS3() {
		$path = $this->path().$this->file;
		echo $path;

		$upload = new Crunchbutton_Upload([
			'file' => $path,
			'resource' => $this->file,
			'bucket' => c::config()->s3->buckets->{'resource'}->name
		]);
		return $upload->upload();
	}

	public function download_url(){
		return Util::url() . '/api/community/resource/download/' . $this->id_community_resource . '/'.$this->file;
	}

	public function path(){
		$path = Util::uploadPath() . '/resource/';
		if ( !file_exists( $path ) ) {
			@mkdir( $path, 0777, true );
		}
		return $path;
	}

	public function doc_path(){
		return Util::uploadPath() . '/resource/' . $this->file;
	}

	public function url(){
		return $this->www() . $this->file;
	}

	public function date(){
		if( !$this->_date ){
			$this->_date = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function communities( $name = false ){
		if( $name ){
			return Crunchbutton_Community::q( 'SELECT community.name, community.id_community FROM community_resource_community INNER JOIN community ON community.id_community = community_resource_community.id_community WHERE id_community_resource = ?', [$this->id_community_resource]);
		} else {
			return Crunchbutton_Community_Resource_Community::q( 'SELECT * FROM community_resource_community WHERE id_community_resource = ?', [$this->id_community_resource]);
		}
	}

	public function byCommunity( $id_community, $type = false ){

		$type = ( $type ) ? ' AND ' . $type . ' = true' : '';

		if( $id_community == 'all' ){
			return Crunchbutton_Community_Resource::q( 'SELECT cr.* FROM community_resource cr WHERE cr.all = true AND active = true ' . $type );
		} else {
			return Crunchbutton_Community_Resource::q( 'SELECT cr.* FROM community_resource cr INNER JOIN community_resource_community crc ON cr.id_community_resource = crc.id_community_resource AND crc.id_community = ?  AND active = true ' . $type, [$id_community]);
		}
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
		$out[ 'path' ] = $this->download_url();
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