<?php

class Crunchbutton_Quote extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('quote')
			->idVar('id_quote')
			->load($id);
	}

	public function date(){
		if( !$this->_date ){
			$this->_date = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function communities( $name = false ){
		if( $name ){
			return Crunchbutton_Community::q( 'SELECT community.name, community.id_community FROM quote_community INNER JOIN community ON community.id_community = quote_community.id_community WHERE id_quote = ?', [$this->id_quote]);
		} else {
			return Crunchbutton_Quote_Community::q( 'SELECT * FROM quote_community WHERE id_quote = ?', [$this->id_quote]);
		}
	}

	public static function byCommunity( $id_community, $type = false ){

		$type = ( $type ) ? ' AND ' . $type . ' = true' : '';

		if( $id_community == 'all' ){
			return Crunchbutton_Quote::q( 'SELECT cr.* FROM quote cr WHERE cr.all = true AND active = true ' . $type );
		} else {
			return Crunchbutton_Quote::q( 'SELECT cr.* FROM quote cr INNER JOIN quote_community crc ON cr.id_quote = crc.id_quote AND crc.id_community = ?  AND active = true ' . $type, [$id_community]);
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
		$out[ 'all' ] = intval( count( $out[ 'communities' ] ) ) == 0 ? true : false;
		$out[ 'active' ] = intval( $out[ 'active' ] ) == 0 ? false: true;
		$out[ 'pages' ] = intval( $out[ 'pages' ] ) == 0 ? false: true;
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
