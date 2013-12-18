<?php
error_reporting(0);

class Controller_test_restaurantSummary extends Crunchbutton_Controller_Account {

	public function init() {

		die( 'remove this die in order to get it working!' );
		$restaurants = Restaurant::q( 'SELECT * FROM restaurant' );
		foreach ( $restaurants as $restaurant ) {
			$hasEmailNotification = false;
			$hasFaxNotification = false;
			$notifications = $restaurant->notifications();
			foreach( $notifications as $notification ){
				if( $notification->active == 1 ){
					if( $notification->type == 'fax' ){
						$hasFaxNotification = true;
						continue;
					}
					if( $notification->type == 'email' ){
						$hasEmailNotification = true;
						continue;
					}
				}
			}

			// update the restaurant table
			if( $hasFaxNotification ){
				$restaurant->summary_method = 'fax';
				$restaurant->save();
				continue;
			}
			if( $hasEmailNotification && !$hasFaxNotification ){
				$restaurant->summary_method = 'email';
				$restaurant->save();
				continue;	
			}

		}
		echo 'ok, it is done!';
	}
}