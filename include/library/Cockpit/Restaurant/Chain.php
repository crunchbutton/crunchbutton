<?php

class Cockpit_Restaurant_Chain extends Crunchbutton_Restaurant_Chain{

	public static function removeChainsByIdCommunityChain( $id_community_chain ){
		c::db()->query( 'DELETE FROM restaurant_chain WHERE id_community_chain = ?', [ $id_community_chain ] );
	}

	public static function byIdCommunityChain( $id_community_chain ){
		return self::q( 'SELECT * FROM restaurant_chain WHERE id_community_chain = ?', [ $id_community_chain ] );
	}

}
