<?php
class Crunchbutton_Cron_Job_CommunityOverloading extends Crunchbutton_Cron_Log {
	public function run(){

			$communities = Community::q('SELECT * FROM community WHERE active = true AND close_all_restaurants = 0 AND close_3rd_party_delivery_restaurants = 0 AND id_driver_group IS NOT NULL');
			foreach($communities AS $community) {
				$orders = Order::q('SELECT * FROM `order` WHERE date>date_sub(now(), interval 1 day) ');
				foreach($orders AS $order) {
					$status = ($order->status()->last()['status']);
					if ($status == 'pickedup') {
						$x++;
					}
					elseif ($status == 'new' || $status == 'accepted') {
						$y++;
						$restaurants[$order->id_restaurant] = true;
					}

				}
				$z = count($restaurants);
				//$drivers = 0;
				$ordercount = $x + $y + $z;
		$drivers = $community->getDriversofCommunity()->count();
		// it always must call finished method at the end
		if ($ordercount && $drivers && ($ordercount/ $drivers) > 8) {
			echo 'too many!';
			//notify cs
			//Crunchbutton_Support::tellCustomerService($message);
			Crunchbutton_Message_Sms::send(['to' => '_PHONE_','message' => $message]);
		}


			}
				$this->finished();
	}

}