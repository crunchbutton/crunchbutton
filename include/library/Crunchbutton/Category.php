<?php
/**
 * Dish categories to group the dishes in a restaurant.
 *
 * @package  Crunchbutton.Category
 * @category model
 *
 * @property int    id_restaurant
 * @property int    id_category
 * @property string name
 * @property int    sort
 */
class Crunchbutton_Category extends Cana_Table {
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function community() {
		return $this->restaurant()->community();
	}

	/**
	 * Return the dishes ffor the current category
	 *
	 * @param array $where Associative array with the WHERE filters
	 *
	 * @return Crunchbutton_Dish[]
	 */
	public function dishes($where = []) {
		if (!isset($this->_dishes)) {
			$defaultFilters = [
				'id_restaurant' => $this->id_restaurant,
				'id_category'   => $this->id_category,
				'active'        => 1,
			];
			$whereSql      = $this->_mergeWhere($defaultFilters, $where);
			$sql           = "SELECT * FROM dish WHERE $whereSql ORDER BY sort, id_dish ASC";
			$this->_dishes = Dish::q($sql);
		}
		return $this->_dishes;
	}

	/**
	 * Exports the category and it's active dishes
	 *
	 * You can overwrite the thishes filter like sending both active and inactive
	 * dishes by sending $where = ['Dish']['active' => NULL];
	 *
	 * @param array $where SQL WHERE filters
	 * @return array
	 */
	public function exports($where = []) {
		$out = $this->properties();

		if (isset($where['Dish'])) {
			$dishes = $this->dishes($where['Dish']);
		} else {
			$dishes = $this->dishes();
		}
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][] = $dish->exports();
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