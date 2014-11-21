<?php

class Cockpit_Admin extends Crunchbutton_Admin {

	public function publicExports() {
		$out = parent::publicExports();
		$out['phone'] = $this->phone;
		foreach ($this->deliveries() as $order) {
			$out['deliveries'][] = [
				'id_order' => $order->id_order,
				'status' => $order->stati[count($order->stati)-1]['status'],
				'update' => $order->stati[count($order->stati)-1]['timestamp']
			];
		}

		return $out;
	}

	public function location() {
		if (!isset($this->_location)) {
			$this->_location = Admin_Location::q('SELECT * FROM admin_location WHERE id_admin="'.$this->id_admin.'" ORDER BY date DESC LIMIT 1')->get(0);
		}
		return $this->_location;
	}

	// return the restaurant the admin could order from #3350
	public function restaurantOrderPlacement(){
		$permission_prefix = 'restaurant-order-placement-';
		$permissions = c::admin()->getAllPermissionsName();
		foreach( $permissions as $permission ){
			if( strpos( $permission->permission, $permission_prefix ) !== false ){
				$id_restaurant = str_replace( $permission_prefix, '', $permission->permission );
				$restaurant = Restaurant::o( $id_restaurant );
				if( $restaurant->id_restaurant ){
					return $restaurant;
				}
			}
		}
		return false;
	}

	public function deliveries() {
		if (!isset($this->_deliveries)) {
			$o = Order::q('
				select o.*, oa.type as status, oa.timestamp as status_time from `order` o
				left join order_action oa using (id_order)
				where
					id_admin="'.$this->id_admin.'"
					and (oa.type="delivery-pickedup" or oa.type="delivery-accepted" or oa.type="delivery-delivered" or oa.type="delivery-rejected" or oa.type="delivery-transfered")
					and o.date >= (curdate() - interval 50 day)
				order by oa.timestamp asc
			');
			$orders = [];
			foreach ($o as $order) {
				if (!$orders[$order->id_order]) {
					$orders[$order->id_order] = $order;
					$orders[$order->id_order]->stati = [];
				}
				$orders[$order->id_order]->stati[] = [
					'status' => $order->status,
					'timestamp' => $order->status_time
				];
			}
			foreach ($orders as $k => $order) {
				$last = count($order->stati) - 1;
				$inactive = ['delivery-rejected', 'delivery-transfered', 'delivery-delivered'];

				if (in_array($order->stati[$last]['status'], $inactive)) {
					unset($orders[$k]);
					continue;
				}
			}
			$this->_deliveries = $orders;
		}
		return $this->_deliveries;
	}
	
	public function exports($params = []) {
		$out = parent::exports($params);
		$out['shifts'] = [];
		$out['working'] = false;

		$next = Community_Shift::nextShiftsByAdmin($this->id_admin);

		if ($next) {
			foreach ($next as $shift) {
				$shift = $shift->exports();
				
				$date = new DateTime($shift['date_start'], new DateTimeZone(c::config()->timezone));
				$start = $date->getTimestamp();
				
				if ($start <= time() ) {
					$date = new DateTime($shift['date_end'], new DateTimeZone(c::config()->timezone));
					$end = $date->getTimestamp();

					$shift['current'] = true;
					$out['working'] = true;
					$out['shift_ends'] = $end - time();

				} else {
					$shift['current'] = false;
				}
				$out['shifts'][] = $shift;
			}
		}

		return $out;
	}

}