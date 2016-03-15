<?php

class Crunchbutton_Cron_Job_ProcessPreOrder extends Crunchbutton_Cron_Log {

	public function run(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( Crunchbutton_Order::PRE_ORDER_INTERVAL );
		$orders = Order::q( 'SELECT * FROM `order` WHERE preordered = 1 AND preorder_processed = 0 AND refunded = 0 AND date_delivery <= ?', [ $now->format( 'Y-m-d H:i:s' ) ] );
		foreach( $orders as $order ){
			if( $order->hasDriverToDeliveryPreOrder() === false ){
				Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
			} else {
				$this->process( $order );
			}
		}
		// it always must call finished method at the end
		$this->finished();
	}

	public function process( $order ){
		$status = $order->status()->last();
		if( $status[ 'status' ] == 'new' ){
			$order->que();
			$order->date = date( 'Y-m-d H:i:s' );
			$order->preorder_processed = 1;
			$order->save();
		}
	}
}
