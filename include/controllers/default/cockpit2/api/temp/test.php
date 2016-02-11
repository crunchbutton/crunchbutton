<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

public function init() {

	$hours = 12;
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$nowString = $now->format( 'Y-m-d H:i:s' );
			$now->modify( '- ' . $hours . ' hours' );
			$interval = $now->format( 'Y-m-d H:i:s' );
			$now->modify( '+ ' . $hours . ' hours' );
			// TODO: Hardwired constant here - not ideal
			$now->modify( '- 3 minutes' );
			$interval3Min = $now->format( 'Y-m-d H:i:s' );
			$now->modify( '+ 3 minutes' );

			$now->modify( '+ 6 hours' );
			$preorder_date = $now->format( 'Y-m-d H:i:s' );


			if (!$admin) {
				$admin = c::admin();
			}
			$deliveryFor = $admin->allPlacesHeDeliveryFor();
			if( count( $deliveryFor ) == 0 ){
				$deliveryFor[] = 0;
			}
			$where = 'o.id_restaurant IN( ' . join( ',', $deliveryFor ) . ' )';

			$query = 'SELECT DISTINCT(o.id_order) as id, o.* FROM `order` as o ' .
				'inner join community as c using (id_community) ' .
				'left outer join order_action as oa on o.delivery_status = oa.id_order_action ' .
				'left outer join order_priority as op on op.id_order = o.id_order
				where (oa.id_order is not null or ' .
				'(op.id_order is null and ((c.delivery_logistics is null) or (o.date < ? and ' .
				'c.delivery_logistics is not null) or (o.preordered=1)))  or (op.id_order is not null and op.priority_expiration < ?) ' .
				'or (op.id_order is not null and op.priority_expiration >= ? and op.id_admin = ? '.
				'and op.priority_given != ?)) and o.delivery_service=true and o.delivery_type = "delivery" and ( o.date > ? OR ( o.preordered = 1 and o.date_delivery < ? AND o.date_delivery > ? ) )'.
				'and ' . $where . ' ORDER BY o.id_order';
//			$op = Crunchbutton_Order_Priority::PRIORITY_LOW;
//			print "The query params: $nowString, $nowString, $admin->id_admin, $op, $interval\n";

			return Order::sq($query, [$interval3Min, $nowString, $nowString, $admin->id_admin,
				Crunchbutton_Order_Priority::PRIORITY_LOW, $interval, $preorder_date, $interval]);

	}
}
