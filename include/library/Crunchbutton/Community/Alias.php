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
		$query = sprintf(" 
				SELECT 
					ca.alias, c.permalink, c.id_community, ca.prep, ca.name_alt, c.loc_lat, c.loc_lon
				FROM 
					community_alias ca 
				INNER JOIN 
					community c ON c.id_community = ca.id_community 
				WHERE ca.alias = '%s'",
		mysql_real_escape_string( $alias ) );
		$res = Cana::db()->query( $query );
		while ( $row = $res->fetch() ) {
			return array( 'id_community' => $row->id_community, 'permalink' => $row->permalink, 'prep' => $row->prep, 'name_alt' => $row->name_alt, 'loc_lat' => $row->loc_lat, 'loc_lon' => $row->loc_lon );
		}
		return false;
	}

	public static function all( $just_fields = false ) {
		 $res = Cana::db()->query('
				SELECT 
					ca.alias, c.permalink, c.id_community, ca.prep, ca.name_alt, c.loc_lat, c.loc_lon
				FROM 
					community_alias ca 
				INNER JOIN community c ON c.id_community = ca.id_community ');
		$aliases = array();
		while ($row = $res->fetch()) {
			$alias = array();
			foreach( $row as $key => $value ){
				if( !$just_fields ){
					$alias[ $key ] = $value;	
				} else {
					if( in_array( $key, $just_fields ) ){
						$alias[ $key ] = $value;	
					}
				}
			}
			$aliases[ $row->alias ] = $alias;
		}
		return $aliases;
	}
}

