<?php

class Cockpit_Category extends Crunchbutton_Category {

		public function dishes() {
		if (!isset($this->_dishes)) {
			$defaultFilters = [
				'id_restaurant' => $this->id_restaurant,
				'id_category'   => $this->id_category
			];
			$whereSql = $this->_mergeWhere($defaultFilters, []);
			$sql = "SELECT * FROM dish WHERE $whereSql ORDER BY sort, id_dish ASC";
			$this->_dishes = Dish::q($sql);
		}
		return $this->_dishes;
	}
}