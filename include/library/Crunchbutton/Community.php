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
	public function restaurants() {
		if (!isset($this->_restaurants)) {
			$this->_restaurants = Restaurant::q('
				select restaurant.* from restaurant
				left join restaurant_community using(id_restaurant)
				where id_community="'.$this->id_community.'"
				and restaurant.active=1
				order by restaurant_community.sort, restaurant.delivery desc
			');

			$this->_restaurants->sort([
				'function' => 'open'
			]);
		}
		return $this->_restaurants;
	}
	
	public function exports() {
		$out = $this->properties();
		foreach ($this->restaurants() as $restaurant) {
			$out['_restaurants'][$restaurant->id_restaurant.' '] = $restaurant->exports();
		}
		return $out;
	}
	
	public static function permalink($permalink) {
		return self::q('select * from community where permalink="'.$permalink.'"')->get(0);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community')
			->idVar('id_community')
			->load($id);
	}
}