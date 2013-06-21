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

								'Stuff for Investors' => array( 
										'active-users-per-week' => 'Active Users per Week',
										'active-users-per-week-by-community' => 'Active Users per Week by Community',
										'churn-rate' => 'Churn Rate - Active Users Lost',
										'new-users-per-week' => 'New Users per Week',
										'new-users-per-active-users' => 'New Users per Active Users',
										'new-users-per-active-users-by-community' => 'New Users per Active Users By Community',
										'new-users-per-week-by-community' => 'New Users per Week by Community',
										'unique-users-per-week' => 'Unique Users per Week',
										'unique-users-per-week-by-community' => 'Unique Users per Week by Community',
										'orders-by-user-week' => 'Orders by Users per Week',
										'orders-per-week' => 'Orders per Week',
										'gross-revenue' => 'Gross Revenue',
										'orders-by-weekday-by-community' => 'Orders by Weekday by Community',
										'active-users-by-community' => 'Active users by community',
									),
							);

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}