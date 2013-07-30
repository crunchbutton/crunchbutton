<?php

class Controller_home extends Crunchbutton_Controller_Account {
	public function init() {
	
		// select count(*) as users from `session` where date_activity>DATE_SUB(NOW(),INTERVAL 10 MINUTE);

		$data = [
			'all' => [
				'orders' => Order::q('select count(*) as c from `order` where env="live"')->c,
				'tickets' => Session_Twilio::q('
					select count(*) as c from support where status="open"
				')->c,
			],
			'day' => [
				'orders' => Order::q('
					select count(*) as c from `order`
					where
						env="live"
						and date > date_sub(now(), interval 24 hour)
				')->c
			],
			'week' => [
				'orders' => Order::q('
					select count(*) as c from `order`
					where
						env="live"
						and date > date_sub(now(), interval 1 week)
				')->c			
			]
		];

		c::view()->data = $data;

		$chartUsers = new Crunchbutton_Chart_User();
		$chartRevenue = new Crunchbutton_Chart_Revenue();
		$chartChurn = new Crunchbutton_Chart_Churn();
		$chartGift = new Crunchbutton_Chart_Giftcard();
		$chartOrder = new Crunchbutton_Chart_Order();
		
		$graphs = [];

		$graphs[ 'Main' ] = array_merge( 
																								$chartUsers->getGroups( 'main' ), 
																								$chartRevenue->getGroups( 'main' ),
																								$chartGift->getGroups( 'main' ),
																								$chartOrder->getGroups( 'main' ),
																								$chartChurn->getGroups( 'main' )
																							);

		$graphs[ 'For Investors' ] = array_merge( 
																								$chartUsers->getGroups( 'investors' ), 
																								$chartRevenue->getGroups( 'investors' ),
																								$chartChurn->getGroups( 'investors' ),
																								$chartGift->getGroups( 'investors' ),
																								$chartOrder->getGroups( 'investors' )
																							);

		$graphs[ 'Detailed Analytics' ] = array_merge( 
																								$chartUsers->getGroups( 'detailed-analytics' ), 
																								$chartRevenue->getGroups( 'detailed-analytics' ),
																								$chartChurn->getGroups( 'detailed-analytics' ),
																								$chartGift->getGroups( 'detailed-analytics' ),
																								$chartOrder->getGroups( 'detailed-analytics' )
																							);
		
		$graphs[ 'Old Graphs' ] = array_merge( 
																								$chartUsers->getGroups(), 
																								$chartRevenue->getGroups(),
																								$chartChurn->getGroups(),
																								$chartGift->getGroups(),
																								$chartOrder->getGroups()
																							);

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}