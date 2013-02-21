<?php

class Controller_api_user_orders extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ($this->method()) {

			case 'get':
				$orders = Order::q('select * from `order` where id_user="'.c::user()->id_user.'" and id_user is not null order by date desc');

				$json = '[';

				$commas = '';

				foreach( $orders as $order ){

					$timezone = new DateTimeZone( $order->restaurant()->timezone );
					
					$date = new DateTime( $order->date );
					$date->setTimeZone( $timezone );
					
					$order->date_tz = $date->format( 'Y-m-d H:i:s' );
					$order->tz = $date->format('T');

					$json .=  $commas . $order->json();
					$commas = ',';

				}

				$json	.= ']';			

				echo $json;
				
				break;

		}
	}
}