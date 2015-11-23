<?php

class Crunchbutton_Community_Alias extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_alias')
			->idVar('id_community_alias')
			->load($id);
	}

	public static function alias( $alias ) {
		$query = '
			SELECT
				ca.alias, c.permalink, c.id_community, ca.prep, ca.name_alt, c.loc_lat, c.loc_lon
			FROM
				community_alias ca
			INNER JOIN
				community c ON c.id_community = ca.id_community
			WHERE ca.alias = :alias
		';
		$res = Cana::db()->query( $query, ['alias' => $alias ] );
		while ( $row = $res->fetch() ) {
			return array( 'id_community' => $row->id_community, 'permalink' => $row->permalink, 'prep' => $row->prep, 'name_alt' => $row->name_alt, 'loc_lat' => $row->loc_lat, 'loc_lon' => $row->loc_lon );
		}
		return false;
	}

	public static function community( $id_community ) {
		$query = "
				SELECT
					ca.alias, c.permalink, c.id_community, ca.prep, ca.name_alt, c.loc_lat, c.loc_lon
				FROM
					community_alias ca
				INNER JOIN
					community c ON c.id_community = ca.id_community
				WHERE c.id_community = " . $id_community . " ORDER BY id_community_alias DESC";
		$res = Cana::db()->query( $query );
		while ( $row = $res->fetch() ) {
			return array( 'id_community' => $row->id_community, 'permalink' => $row->permalink, 'prep' => $row->prep, 'name_alt' => $row->name_alt, 'loc_lat' => $row->loc_lat, 'loc_lon' => $row->loc_lon );
		}
		return false;
	}

	public function exports(){
		$out = $this->properties();
		unset( $out[ 'id_community' ] );
		unset( $out[ 'id' ] );
		return $out;
	}

	public static function all( $just_fields = false ) {
		 $res = Cana::db()->query('
				SELECT
					ca.alias, c.permalink, c.id_community, ca.prep, ca.name_alt, c.loc_lat, c.loc_lon, c.image
				FROM
					community_alias ca
				INNER JOIN community c ON c.id_community = ca.id_community
				UNION
				SELECT c.permalink as alias, c.permalink, c.id_community, c.prep,  c.name, c.loc_lat, c.loc_lon, c.image  FROM community c WHERE c.active = 1 AND c.permalink IS NOT NULL');
		$aliases = array();
		while ($row = $res->fetch()) {
			$alias = array();
			foreach( $row as $key => $value ){
				if( !$just_fields ){
					$alias[ $key ] = $value;
				} else {
					if( in_array( $key, $just_fields ) ){
						if( is_numeric( $value ) ){
							$value = floatval( $value );
						}
						$store = true;
						if( $key == 'image' && !$value ){
							$store = false;
						}
						if( $store ){
							$alias[ $key ] = $value;
						}
					}
				}
			}
			$aliases[ $row->alias ] = $alias;
		}
		return $aliases;
	}
}

