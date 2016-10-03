<?php

class Crunchbutton_Order_Eta extends Cana_Table {

	const METHOD_SMART_ETA = 'smart-eta';
	const METHOD_INFORMED_ETA = 'informed-eta';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_eta')
			->idVar('id_order_eta')
			->load($id);
	}

	public function exports() {
		return [
			'distance' => $this->distance,
			'time' => $this->time,
			'date' => $this->date
		];
	}

	public static function create($order, $method = null) {
		// right now we only have one method. google

		$method = 'google-directions-php';

		$ret = self::_methodGoogle($order);

		$eta = new Order_Eta([
			'id_order' => $order->id_order,
			'time' => $ret->time,
			'distance' => $ret->distance,
			'date' => date('Y-m-d H:i:s'),
			'method' => $method
		]);
		$eta->save();

		return $eta;
	}

	public function _smartEta( $order ){
		if( !Crunchbutton_Order_Eta::hasSmartETA( $order->id_order ) ){
			$restaurant = $order->restaurant();
			$time = $restaurant->smartETA();
			if( $time <= 0 ){
				return;
			}
			$eta = new Order_Eta([
				'id_order' => $order->id_order,
				'time' => $time,
				'distance' => null,
				'date' => date('Y-m-d H:i:s'),
				'method' => Crunchbutton_Order_Eta::METHOD_SMART_ETA
			]);
			$eta->save();
		}
	}

	private static function _methodGoogle($order) {
		$status = $order->status()->last()['status'];

		if (!$status) {
			$time = $order->restaurant()->delivery_estimated_time;
		} elseif ($status == 'delivered') {
			$time = 0;
		} else {
			$ret = self::_getGoogleEta($order);

			$time = $ret->time;

			if ($status == 'accepted' || $status == 'transferred') {
				if ($order->restaurant()->formal_relationship == 1 || $order->restaurant()->order_notifications_sent) {
					$time += 5;
				} else {
					$time += 15;
				}
			}
		}

		return (object)[
			'time' => $time,
			'distance' => $ret->distance
		];
	}

	private static function _getGoogleEta($order) {

		$status = $order->status()->last()['status'];

		// if it doesn't have a driver there is no way to know the distance
		if( !$order->driver()->id_admin ){
			return;
		}

		$driver = $order->driver()->location()->lat.','.$order->driver()->location()->lon;
		$customer = urlencode($order->address);
		$restaurant = $order->restaurant()->loc_lat.','.$order->restaurant()->loc_long;

		$url = 'https://maps.googleapis.com/maps/api/directions/json?';

		if ($status == 'pickedup') {
			$url .= 'origin='.$driver.'&destination='.$customer;

		} elseif ($status == 'accepted' || $status == 'transfered') {
			$url .= 'origin='.$driver.'&destination='.$customer.'&waypoints='.$restaurant;
		}

		// $url = 'https://maps.googleapis.com/maps/api/directions/json?origin=33.9848,-118.446&destination=1120%20princeton,%20marina%20del%20rey%20ca%2090292&waypoints=33.1751,-96.6778';

		$res = @json_decode(@file_get_contents($url));
		$eta = 0;
		$distance = 0;

		if ($res && $res->routes[0] && $res->routes[0]->legs) {
			foreach ($res->routes[0]->legs as $leg) {
				$eta += $leg->duration->value/60;
				$distance += $leg->distance->value * 0.000621371;
			}
		}

		return (object)[
			'time' => $eta,
			'distance' => $distance
		];

		//&key=API_KEY
	}

	public function registerSmartETA(){
		$query = "SELECT * FROM `order` o WHERE o.delivery_type = '" . Crunchbutton_Order::SHIPPING_DELIVERY . "' AND o.delivery_service = true AND o.date > DATE_SUB( NOW(), INTERVAL 10 minute ) ORDER BY o.id_order ASC";
		$orders = Crunchbutton_Order::q( $query );
		foreach( $orders as $order ){
			Crunchbutton_Order_Eta::_smartEta( $order );
		}
	}

	public function hasSmartETA( $id_order ){
		$query = 'SELECT * FROM order_eta WHERE id_order = "' . $id_order . '" AND method = "' . Crunchbutton_Order_Eta::METHOD_SMART_ETA . '"';
		$eta = Crunchbutton_Order_Eta::q( $query )->get( 0 );
		if( $eta->id_order_eta ){
			return true;
		}
		return false;
	}

	public static function informedEtaByOrder( $id_order ){
		$query = 'SELECT * FROM order_eta WHERE id_order = "' . $id_order . '" AND method = "' . Crunchbutton_Order_Eta::METHOD_INFORMED_ETA . '"';
		$eta = Crunchbutton_Order_Eta::q( $query )->get( 0 );
		if( $eta->time ){
			return intval( $eta->time );
		}
		return false;
	}

	public function order() {
		return Order::o($this->id_order);
	}
}
