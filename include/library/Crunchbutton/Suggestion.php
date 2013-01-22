<?php

class Crunchbutton_Suggestion extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('suggestion')
			->idVar('id_suggestion')
			->load($id);
	}
	
	public static function find($search = []) {
		$query = 'SELECT `suggestion`.* FROM `suggestion` LEFT JOIN restaurant USING(id_restaurant) WHERE id_suggestion IS NOT NULL ';
		
		if ($search['type']) {
			$query .= ' and type="'.$search['type'].'" ';
		}
		
		if ($search['status']) {
			$query .= ' and status="'.$search['status'].'" ';
		}
		
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
		}

		if ($search['restaurant']) {
			$query .= ' and `suggestion`.id_restaurant="'.$search['restaurant'].'" ';
		}

		if ($search['community']) {
			$query .= ' and `suggestion`.id_community="'.$search['community'].'" ';
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `suggestion`.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and `suggestion`.content not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`suggestion`.name like "%'.$word.'%"
						or `suggestion`.content like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= 'ORDER BY `date` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$suggestions = self::q($query);
		return $suggestions;
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function community() {
		return Community::o($this->id_community);
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}

	public function save() {
		parent::save();
	}
}