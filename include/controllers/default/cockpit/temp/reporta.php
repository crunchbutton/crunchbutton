<?php

class Controller_temp_reporta extends Crunchbutton_Controller_Account {
	public function init() {
		$query = "SELECT DISTINCT( o.id_order ) AS id_order, r.name AS 'restaurant', c.name AS 'community', a.name AS 'driver', oa.id_order_action, o.refunded FROM `order` o 
							INNER JOIN order_action oa ON oa.id_order = o.id_order 
							INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community
							INNER JOIN admin a ON oa.id_admin = a.id_admin
							WHERE 
							o.id_order >= 15827 
							AND r.name NOT LIKE '%test%'
							AND o.name NOT LIKE '%test%'
							AND oa.type = 'delivery-delivered' ORDER BY o.id_order ASC";

		$orders = c::db()->get( $query );

		$rows = [];
		$headers = [ 'id_order', 'restaurant', 'community', 'driver', 'refunded', 'ordered_date', 'ordered_hour', 'delivered_hour' ];

		echo implode( ',', $headers )."\n";

		foreach( $orders as $order ){

			ob_start();

			$_order = Crunchbutton_Order::o( $order->id_order );
			$ordered_at = $_order->date();
			$ordered_at->setTimeZone( new DateTimeZone( c::config()->timezone ) );
			
			$action = Crunchbutton_Order_Action::o( $order->id_order_action );
			$delivered_at = $action->dateAtTz(  c::config()->timezone  );

			$refunded = ( $order->refunded ) ? 'Yes' : 'No';

			$rows = array( 	'"' . $order->id_order . '"',
											'"' . $order->restaurant . '"',
											'"' . $order->community . '"',
											'"' . $order->driver . '"',
											'"' . $refunded . '"',
											'"' . $ordered_at->format( 'M jS Y' ) . '"',
											'"' . $ordered_at->format( 'g:i:s A' ) . '"',
											'"' . $delivered_at->format( 'g:i:s A' ) . '"', );
			echo implode( ',', $rows )."\n";
			$output = ob_get_clean();
			// $output = preg_replace('/\n|\r|\t/i', '', $output);
			echo "$output\n";
		}
	}
}