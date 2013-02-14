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
					ca.alias, c.permalink 
				FROM 
					community_alias ca 
				INNER JOIN 
					community c ON c.id_community = ca.id_community 
				WHERE ca.alias = '%s'",
		mysql_real_escape_string( $alias ) );
		$res = Cana::db()->query( $query );
		while ( $row = $res->fetch() ) {
			return [ $row->alias => $row->permalink ];
		}
		return false;
	}

	public static function all() {
		 $res = Cana::db()->query('
				SELECT 
					ca.alias, c.permalink 
				FROM 
					community_alias ca 
				INNER JOIN community c ON c.id_community = ca.id_community ');
		$aliases = array();
		while ($row = $res->fetch()) {
			$aliases[ $row->alias ] = $row->permalink;
		}
		return $aliases;
	}

}

