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
										'new-users-per-week' => 'New users per week',
										'new-users-per-week-community' => 'New users per week by community',
										'active-users-per-week' => 'Active users per week',
										'active-users-per-week-by-community' => 'Active users per week by community',
										'users-per-week-by-community' => 'Users per week by community',
										'active-users-by-community' => 'Active users by community',
										'orders-by-user-week' => 'Orders by user per week',
										'users-per-week' => 'Users per week',
										'orders-per-week' => 'Orders per week',
										'gross-revenue' => 'Gross revenue',
										'orders-by-date-by-community' => 'Orders by week day by community'
									);

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}