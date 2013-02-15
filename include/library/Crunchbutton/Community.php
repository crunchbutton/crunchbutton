<?php

class Crunchbutton_Community extends Cana_Table {
	public static function all($force = null) {
		$ip = preg_replace('/[^0-9\.]+/','',$_SERVER['REMOTE_ADDR']);
		$force = preg_replace('/[^a-z\-]+/','',$force);
		if ($force) {
			$forceq = ' OR (community.permalink="'.c::db()->escape($force).'") ';
		}

		$q = '
			select community.* from community
			left join community_ip on community_ip.id_community=community.id_community
			where
				community.active=1
				AND (
					( community.private=0 )
					OR
					(community.private=1 AND community_ip.ip="'.c::db()->escape($ip).'")
					'.$forceq.'
				)
			group by community.id_community
			order by name
		';

		return self::q($q);
	}

	/**
	 * Returns all the restaurants that belong to this Community
	 *
	 * @return Cana_Iterator
	 *
	 * @todo probably not required to sort them as the front end sorts them
	 */
	public function restaurants() {
		if (!isset($this->_restaurants)) {
			$this->_restaurants = Restaurant::q('
				SELECT
					restaurant.*
					, restaurant_community.sort
				FROM restaurant
					left join restaurant_community using(id_restaurant)
				WHERE
						id_community="'.$this->id_community.'"
					and restaurant.active=1
				ORDER by
					restaurant_community.sort,
					restaurant.delivery DESC
			');

			$this->_restaurants->sort([
				'function' => 'open'
			]);
		}
		return $this->_restaurants;
	}

	public function exports() {
		$out = $this->properties();
		$out[ 'name_alt' ] = $this->name_alt();
		$out[ 'prep' ] = $this->prep();
		foreach ($this->restaurants() as $restaurant) {
			$out['_restaurants'][$restaurant->id_restaurant.' '] = $restaurant->exports();
		}
		return $out;
	}

	public static function permalink($permalink) {
		return self::q('select * from community where permalink="'.$permalink.'"')->get(0);
	}

	public static function all_locations(){
		$res = Cana::db()->query( 'SELECT c.id_community, c.loc_lat, c.loc_lon FROM community c' );
		$locations = array();
		while ( $row = $res->fetch() ) {
			$locations[ $row->id_community ] = array( 'loc_lat' => $row->loc_lat, 'loc_lon' => $row->loc_lon );
		}
		return $locations;
	}

	public function name_alt(){
		$alias = Community_Alias::alias( $this->permalink );
		if( !$alias ){
			$alias = Community_Alias::community( $this->id_community );
		}
		if( $alias ){
			return $alias[ 'name_alt' ];
		}
		return false;
	}

	public function prep(){
		$alias = Community_Alias::alias( $this->permalink );
		if( !$alias ){
			$alias = Community_Alias::community( $this->id_community );
		}
		if( $alias ){
			return $alias[ 'prep' ];
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community')
			->idVar('id_community')
			->load($id);
	}

	/**
	 * Returns the Testing community
	 *
	 * @return Crunchbutton_Community
	 */
	public function getTest()
	{
		$row = $this->q('SELECT * FROM community WHERE name="Testing" ')->current();
		return $row;
	}

}