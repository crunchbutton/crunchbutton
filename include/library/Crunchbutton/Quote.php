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

	public function restaurants( $name = false ){
		if( $name ){
			return Crunchbutton_Restaurant::q( 'SELECT restaurant.name, restaurant.id_restaurant FROM quote_restaurant INNER JOIN restaurant ON restaurant.id_restaurant = quote_restaurant.id_restaurant WHERE id_quote = ?', [$this->id_quote]);
		} else {
			return Crunchbutton_Quote_Restaurant::q( 'SELECT * FROM quote_restaurant WHERE id_quote = ?', [$this->id_quote]);
		}
	}

	public static function byRestaurant( $id_restaurant, $type = false ){

		$type = ( $type ) ? ' AND ' . $type . ' = true' : '';

		if( $id_restaurant == 'all' ){
			return Crunchbutton_Quote::q( 'SELECT cr.* FROM quote cr WHERE cr.all = true AND active = true ' . $type );
		} else {
			return Crunchbutton_Quote::q( 'SELECT cr.* FROM quote cr INNER JOIN quote_restaurant crc ON cr.id_quote = crc.id_quote AND crc.id_restaurant = ?  AND active = true ' . $type, [$id_restaurant]);
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
		$restaurants = $this->restaurants();
		if( $restaurants ){
			$out[ 'restaurants' ] = [];
			foreach( $restaurants as $restaurant ){
				$out[ 'restaurants' ][] = $restaurant->id_restaurant;
			}
		}
		$out[ 'all' ] = intval( count( $out[ 'communities' ] ) ) == 0 ? true : false;
		$out[ 'all_restaurants' ] = intval( count( $out[ 'restaurants' ] ) ) == 0 ? true : false;
		$out[ 'active' ] = intval( $out[ 'active' ] ) == 0 ? false: true;
		$out[ 'pages' ] = intval( $out[ 'pages' ] ) == 0 ? false: true;
		if( $out[ 'facebook_id' ] ){
			$out[ 'image' ] = 'https://graph.facebook.com/' . $out[ 'facebook_id' ] . '/picture?width=120&height=120';
		}
		return $out;
	}

	public static function publicExports(){
		$out = [];
		$quotes = Crunchbutton_Quote::q( 'SELECT * FROM quote WHERE active =  true' );
		foreach( $quotes as $quote ){
			$q = $quote->exports();
			unset( $q[ 'id' ] );
			unset( $q[ 'id_quote' ] );
			unset( $q[ 'facebook_id' ] );
			unset( $q[ 'active' ] );
			unset( $q[ 'id_admin' ] );
			if( $q[ 'all' ] ){
				unset( $q[ 'communities' ] );
			}
			if( $q[ 'all_restaurants' ] ){
				unset( $q[ 'restaurants' ] );
			}
			$out[] = $q;
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
