<?php

class Cockpit_Community_Chain extends Crunchbutton_Community_Chain{

	public static function byCommunity( $id_community ){
		return self::q( 'SELECT * FROM community_chain cc INNER JOIN chain c ON c.id_chain = cc.id_chain WHERE cc.id_community = ? ORDER BY c.name ASC', [ $id_community ] );
	}

	public static function byCommunityChain( $id_community, $id_chain ){
		return self::q( 'SELECT * FROM community_chain WHERE id_community = ? AND id_chain = ? ORDER BY id_community_chain DESC LIMIT 1', [ $id_community, $id_chain ] )->get( 0 );
	}

	public function exports(){
		$out = $this->properties();
		$restaurant = Cockpit_Restaurant_Chain::byIdCommunityChain( $this->id_community_chain );
		if( count( $restaurant ) ){
			$restaurant = $restaurant->get( 0 );
			if( $restaurant->id_restaurant ){
				$restaurant = $restaurant->restaurant();
				$out[ 'id_restaurant' ] = $restaurant->id_restaurant;
				$out[ 'restaurant' ] = $restaurant->name;
				$out[ 'linked_restaurant' ] = true;
			}
		}
		return $out;
	}
}
