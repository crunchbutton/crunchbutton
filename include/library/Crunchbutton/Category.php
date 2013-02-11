<?php

class Crunchbutton_Category extends Cana_Table {
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function community() {
		return $this->restaurant()->community();
	}

	public function dishes($where = []) {
		if (!isset($this->_dishes)) {
			$defaultFilters = [
				'id_category' => $this->id_category,
				'active'      => 1,
			];
			if (isset($_SESSION['admin'])) {
				$where['active'] = NULL;
			}
			$whereSql = $this->_mergeWhere($defaultFilters, $where);

			$this->_dishes = Dish::q("SELECT * FROM dish WHERE $whereSql ORDER BY sort DESC");
		}
		return $this->_dishes;
	}

	public function exports() {
		$out = $this->properties();
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][$dish->id_dish] = $dish->exports();
		}
		return $out;
	}

	public function name() {
		return $this->name.($this->loc ? (' '.$this->community()->prep.' '.$this->community()->name) : '');
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('category')
			->idVar('id_category')
			->load($id);
	}
}