<?php

class Controller_sheriff extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'curation'])) {
			return ;
		}

		$orderByCategory = c::getPagePiece(2) == 'category';
		$showInactive = $_REQUEST['showInactive'] ? $_REQUEST['showInactive'] : 0;

		$totalFoodOrdered = c::db()->get('SELECT COUNT(*) AS total FROM `order` o INNER JOIN order_dish od ON od.id_order = o.id_order WHERE o.name NOT LIKE "%test%" ');
		$totalFoodOrdered = intval($totalFoodOrdered->get(0)->total);

		$data = [];
		
		$sheriff = new Sheriff;
		$restaurants = $sheriff->restaurants();


		foreach ($restaurants as $restaurant) {
			$data[$restaurant->id_restaurant] = [];
			$data[$restaurant->id_restaurant]['Name'] = $restaurant->name;
			$data[$restaurant->id_restaurant]['Food'] = $sheriff->foodReport([
				'id_restaurant' => $restaurant->id_restaurant
			]);
		}

		c::view()->totalFoodOrdered = $totalFoodOrdered;
		c::view()->data = $data;
		c::view()->display('home/curation');

	}
}