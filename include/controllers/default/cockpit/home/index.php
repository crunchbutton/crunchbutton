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
										/* OK */
										'orders-per-' => array(
																							'_title' => 'Orders per',
																							'week' => 'Week', 
																							'month' => 'Month', 
																						),
										/* OK */
										'new-users-per-' => array( 
																						'_title' => 'New Users per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
										/* OK */
										'gross-revenue-per-' => array( 
																						'_title' => 'Gross Revenue per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
										/* OK */
										'active-users-per-' => array( 
																						'_title' => 'Active users per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
										/* OK */
										'unique-users-per-' => array( 
																						'_title' => 'Unique Users per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
										/* OK */
										'orders-by-user-per-' => array( 
																						'_title' => 'Orders by Users per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
										/* OK */
										'new-users-per-active-users-per-' => array( 
																						'_title' => 'New Users per Active Users per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																					),
										/* OK */
										'churn-rate-per-' => array( 
																						'_title' => 'Churn Rate per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
										/* OK */
										'churn-rate-per-active-user-per-' => array( 
																						'_title' => 'Churn Rate per Active User per',
																						'week' => 'Week', 
																						'month' => 'Month', 
																						),
																						
										'active-users-per-week-by-community' => 'Active Users per Week by Community',
										'new-users-per-week-by-community' => 'New Users per Week by Community',
										'new-users-per-active-users-by-community' => 'New Users per Active Users By Community',
										'unique-users-per-week-by-community' => 'Unique Users per Week by Community',
										'repeat-orders-per-active-user' => 'Repeat Orders per Active User',
										'active-users-by-community' => 'Active Users by Community',
										'reclaimed-users' => 'Reclaimed Users',
									),
								'Tracking Marketing Efforts' => array(
										'active-users-per-week' => 'Active Users per Week',
										'active-users-per-week-by-community' => 'Active Users per Week by Community',
										'orders-by-weekday-by-community' => 'Orders by Weekday by Community',
										'orders-per-week' => 'Orders per Week',
										'orders-per-week-by-community' => 'Orders per Week by Community',
										// 'orders-using-giftcard-per-week' => 'Orders using Gift Card per Week',*/
									),
							);

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}