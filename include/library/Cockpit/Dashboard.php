<?php

class Cockpit_Dashboard extends Cana_Table {

	public function __construct($id_community){
		$this->community($id_community);
	}
	public static function communitiesWithShits(){
		$query = 'SELECT DISTINCT(community.id_community) FROM community_shift
								INNER JOIN community ON community.id_community = community_shift.id_community
								WHERE date_start BETWEEN SUBDATE( NOW(), INTERVAL 12 HOUR) AND ADDDATE( NOW(), INTERVAL 12 HOUR ) AND community.id_community != 92 AND community_shift.active = 1 ORDER BY community.name ASC';
		$communities = c::db()->get( $query );
		$out = [];
		if(count($communities)){
			foreach($communities as $community){
				$out[] = $community->id_community;
			}
		}
		return $out;
	}

	public function community($id_community=null){
		if($id_community){
			if(is_numeric($id_community)){
				$this->community = Community::o($id_community);
			} else {
				$this->community = Community::permalink($id_community);
			}
		}
		return $this->community;
	}

	public function statusByCommunity(){
		$out = [];
		$community = $this->community();
		$orders_by_drivers = [];
		$now = new DateTime( 'now', new DateTimeZone( $community->timezone ) );
		$out['community'] = ['id_community' => $community->id_community, 'name' => $community->name, 'permalink' => $community->permalink, 'timezone' => $community->timezone, 'current' => $now->format('Y-m-d H:i')];
		$orders = $this->lastOrders();
		$shifts = $this->workingDriversByCommunity();
		$out['orders'] = ['unaccepted' => [], 'undelivered' => [], 'in_transit' => []];
		foreach ($orders as $order) {
			if(!$order['driver_login']){
				$out['orders']['unaccepted'][] = $order;
			} else {
				$out['orders']['in_transit'][] = $order;
			}
			if($order['order_status'] != 'delivery-canceled' && $order['order_status'] !=  'delivery-delivered'){
				$out['orders']['undelivered'][] = $order;
				if($order['driver_login']){
					if(!$orders_by_drivers[$order['driver_login']]){
						$orders_by_drivers[$order['driver_login']] = 0;
					}
					$orders_by_drivers[$order['driver_login']]++;
				}
			}
		}
		$out['shifts_today'] = $shifts;
		$out['current_drivers'] = [];
		if(count($shifts)){
			foreach ($shifts as $shift) {
				$shift['orders'] = $orders_by_drivers[$shift['login']] ? $orders_by_drivers[$shift['login']] : 0;
				if($shift['current']){
					$out['current_drivers'][] = $shift;
				}
			}
		}
		$out['total_shifts_today'] = count($out['shifts_today']);
		$out['total_current_drivers'] = count($out['current_drivers']);
		$out['total_unaccepted_orders'] = count($out['orders']['unaccepted']);
		$out['total_undelivered_orders'] = count($out['orders']['undelivered']);
		$out['total_in_transit_orders'] = count($out['orders']['in_transit']);
		return $out;
	}

	public function lastOrders(){
		$community = $this->community();
		$query = 'SELECT
								o.id_order,
								o.name AS customer,
								o.phone AS customer_phone,
								o.date AS order_date,
								r.name AS restaurant,
								r.permalink AS restaurant_permalink,
								a.name AS driver,
								a.login AS driver_login,
								a.phone AS driver_phone,
								oa.timestamp AS order_status_date,
								oa.type AS order_status,
								o.preordered_date,
								o.date_delivery
							FROM `order` o
							INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							LEFT JOIN order_action oa ON o.delivery_status = oa.id_order_action
							LEFT JOIN admin a on oa.id_admin = a.id_admin
							WHERE o.id_community = ? AND ( o.date > SUBDATE( NOW(), INTERVAL 6 HOUR) OR o.preordered_date > SUBDATE( NOW(), INTERVAL 6 HOUR) )';
		$orders = c::db()->get( $query, [$community->id_community] );
		$out = [];
		if($orders){
			foreach($orders as $order){
				$_order = [ 'id_order' => $order->id_order,
										'customer' => $order->customer,
										'customer_phone' => $order->customer_phone,
										'order_date' => $order->order_date,
										'restaurant' => $order->restaurant,
										'restaurant_permalink' => $order->restaurant_permalink,
										'id_admin' => $order->id_admin,
										'driver' => $order->driver,
										'driver_login' => $order->driver_login,
										'driver_phone' => $order->driver_phone,
										'order_status' => $order->order_status,
										'preordered_date' => $order->preordered_date,
										'date_delivery' => $order->date_delivery];
				foreach($_order as $key => $val){
					if(!$val){
						unset($_order[$key]);
					}
				}
				$out[] = $_order;
			}
		}
		return $out;
	}

	private function lastActionByDriver($id_admin){
		$query = 'SELECT timestamp as date, type, id_order FROM order_action
							WHERE
								id_admin = ?
							AND type IN ("delivery-pickedup","delivery-accepted","delivery-rejected","delivery-delivered") AND timestamp > SUBDATE( NOW(), INTERVAL 6 HOUR)
							ORDER BY id_order_action DESC LIMIT 1';
		$action = c::db()->get($query, [$id_admin])->get(0);
		if($action->id_order){
			return ['date' => $action->date, 'type' => $action->type, 'id_order' => $action->id_order];
		}
		return null;
	}

	public function workingDriversByCommunity(){
		$community = $this->community();
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$timezone = ( $community->timezone ) ? $community->timezone : c::config()->timezone;
		$community_tz = new DateTimeZone( $timezone );
		$now->setTimezone( $community_tz );
		$now_formated = $now->format( 'Y-m-d' );
		$query = 'SELECT a.login, a.name, a.phone, cs.date_start, cs.date_end, asa.confirmed, a.id_admin FROM community_shift cs
								INNER JOIN admin_shift_assign asa ON cs.id_community_shift = asa.id_community_shift
								INNER JOIN admin a ON asa.id_admin = a.id_admin AND a.active = true
							WHERE
								cs.id_community = ?
								AND date(cs.date_start) = ?
								AND cs.active = true
							ORDER BY cs.date_start';
		$drivers = c::db()->get( $query, [$community->id_community, $now_formated] );
		$out = [];
		if($drivers){
			foreach ($drivers as $driver) {
				$start = DateTime::createFromFormat('Y-m-d H:i:s', $driver->date_start, $community_tz);
				$end = DateTime::createFromFormat('Y-m-d H:i:s', $driver->date_end, $community_tz);
				$current = false;
				if($now >= $start && $now <= $end){
					$current = true;
				}
				$last_action = $this->lastActionByDriver($driver->id_admin, $now_formated);
				$out[] = ['login' => $driver->login,
									'name' => $driver->name,
									'phone' => $driver->phone,
									'date_start' => $driver->date_start,
									'date_end' => $driver->date_end,
									'confirmed' => ($driver->confirmed == '1' ? true : false),
									'current' => $current,
									'last_action' => $last_action
									];
			}
		}
		return $out;
	}
}
