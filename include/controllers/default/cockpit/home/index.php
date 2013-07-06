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

		$graphs = array( 
								'' => array( 
									// Groups
									array( 'divId' => 'chart-orders', 'title-group' => 'Orders', 'chart-url' => 'orders-per-week' ),
									array( 'divId' => 'chart-users', 'title-group' => 'Users', 'chart-url' => 'users-new-per-day' ),
									array( 'divId' => 'chart-gross', 'title-group' => 'Gross Revenue', 'chart-url' => 'gross-revenue-per-week' ),
									array( 'divId' => 'chart-churn', 'title-group' => 'Churn Rate', 'chart-url' => 'churn-rate-per-week' ),
									array( 'divId' => 'chart-gift-cards', 'title-group' => 'Gift Cards', 'chart-url' => 'gift-cards-created-per-day' ),
									)
							);

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}