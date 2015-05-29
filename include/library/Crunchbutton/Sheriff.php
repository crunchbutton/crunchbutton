<?php
class Crunchbutton_Sheriff extends Cana_Model {
	public function restaurants() {
		return Restaurant::q('SELECT * FROM restaurant WHERE active = true and id_restaurant=true ORDER BY name ASC');
		
			
			/*
		} else {
			$_restaurants_id = c::admin()->getRestaurantsUserHasCurationPermission();
			$in = join( ',', $_restaurants_id );
			$restaurants = Restaurant::q("SELECT * FROM restaurant WHERE active = true AND id_restaurant IN( {$in} ) ORDER BY name ASC");
		}
		*/
	}

	public function foodReport($params = []) {
	
//		 $orderByCategory = false, $showInactive = false 

		if( $orderByCategory ){
			$orderBy = 'ORDER BY c.sort ASC, total DESC';
		} else {
			$orderBy = 'ORDER BY total DESC';
		}

		if( !$showInactive ){
			$active = ' AND d.active = 1 ';
		}

		$query = '
			SELECT 
				d.id_dish AS id_dish,
				d.name AS dish,
				c.name AS category,
				ordered.total AS times,
				d.active AS active,
				first_order.date  AS date,
				top,
				DATE_FORMAT( first_order.date ,"%b %d %Y")  AS date_formated
			FROM dish d
			INNER JOIN category c ON c.id_category = d.id_category
			LEFT JOIN
				(SELECT COUNT(*) total,
								id_dish
				 FROM order_dish od
				 INNER JOIN `order` o ON o.id_order = od.id_order AND o.name NOT LIKE "%test%"
				 GROUP BY id_dish) ordered ON ordered.id_dish = d.id_dish
			LEFT JOIN
				(SELECT min(date) AS date,
								od.id_dish AS id_dish
				 FROM order_dish od
				 INNER JOIN `order` o ON od.id_order = o.id_order
				 GROUP BY od.id_dish) first_order ON first_order.id_dish = d.id_dish
			WHERE d.id_restaurant = '.$params['id_restaurant'].' '.$active.' '.$orderBy.'
			limit 9
		';

		$foods = c::db()->get( $query );

		$data = [];

		$query = 'SELECT COUNT(*) AS total FROM `order` o WHERE o.id_restaurant = '.$params['id_restaurant'].' AND o.name NOT LIKE "%test%" ';
		$totalOrder = c::db()->get( $query );
		$totalOrder = $totalOrder->_items[0]->total;

		$query = 'SELECT COUNT(*) AS total FROM `order` o INNER JOIN order_dish od ON od.id_order = o.id_order WHERE o.id_restaurant = '.$params['id_restaurant'].' AND o.name NOT LIKE "%test%" ';
		$restaurant_ordered = c::db()->get( $query );
		$restaurant_ordered = $restaurant_ordered->_items[0]->total;

		foreach( $foods as $food ){

			$query = 'SELECT COUNT( DISTINCT( o.phone ) ) AS total FROM order_dish od  INNER JOIN `order` o ON o.id_order = od.id_order AND o.name NOT LIKE "%test%" WHERE od.id_dish = "'.$food->id_dish.'" ';
			$unique_users = c::db()->get( $query );
			$unique_users = $unique_users->_items[0]->total;

			$query = 'SELECT COUNT(*) AS total FROM `order` o INNER JOIN order_dish od ON od.id_order = o.id_order WHERE o.date  >= "'.$food->date.'" AND o.id_restaurant = '.$params['id_restaurant'].' AND o.name NOT LIKE "%test%" ';
			$restaurant_orderedSince = c::db()->get( $query );
			$restaurant_orderedSince = $restaurant_orderedSince->_items[0]->total;
		
			$query = 'SELECT COUNT(*) AS total FROM `order` o WHERE o.date  >= "'.$food->date.'" AND o.id_restaurant = '.$params['id_restaurant'].' AND o.name NOT LIKE "%test%" ';
			$ordersSince = c::db()->get( $query );
			$ordersSince = $ordersSince->_items[0]->total;

			$all_orderedSince = Crunchbutton_Order_Dish::totalDishesSince( $food->date );

			$data[] = array( 'name' => $food->dish, 'times' => $food->times, 'category' => $food->category, 'active' => $food->active, 'date' => $food->date, 'restaurant_orderedSince' => $restaurant_orderedSince, 'ordersSince' => $ordersSince, 'restaurant_ordered' => $restaurant_ordered, 'totalOrder' => $totalOrder, 'all_orderedSince' => $all_orderedSince, 'date_formated' => $food->date_formated, 'unique_users' => $unique_users );
		}
		return $data;
	}
}