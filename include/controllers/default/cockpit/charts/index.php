<?php

class Controller_charts extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'metrics-manage-cohorts'])) {
			return ;
		}
		switch ( c::getPagePiece(1) ) {

			case 'cohort':
				switch ( c::getPagePiece(2) ) {
					case 'new':
							$this->cohort_new();
							break;
					case 'remove':
							$this->cohort_remove();
							break;
					default:
						$this->cohort();
						break;
				}

				break;

			default:
				$this->overview();
				break;
		}
	}

	public function overview() {

		// select count(*) as users from `session` where date_activity>DATE_SUB(NOW(),INTERVAL 10 MINUTE);

		$data = [
			'all' => [
				'orders' => Order::q('select count(*) as c from `order` where env="live"')->c,
				'tickets' => Crunchbutton_Support::pendingSupport()->count(),
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

		if ( c::admin()->permission()->check( ['global','metrics-all','metrics-main'] ) ) {

			$graphs[ 'Main' ] = array_merge(
																								$chartUsers->getGroups( 'main' ),
																								$chartRevenue->getGroups( 'main' ),
																								$chartGift->getGroups( 'main' ),
																								$chartOrder->getGroups( 'main' ),
																								$chartChurn->getGroups( 'main' )
																							);
		}

		if ( c::admin()->permission()->check( ['global','metrics-all','metrics-investors'] ) ) {

			$graphs[ 'For Investors' ] = array_merge(
																								$chartUsers->getGroups( 'investors' ),
																								$chartRevenue->getGroups( 'investors' ),
																								$chartChurn->getGroups( 'investors' ),
																								$chartGift->getGroups( 'investors' ),
																								$chartOrder->getGroups( 'investors' )
																							);
		}

		if ( c::admin()->permission()->check( ['global','metrics-all','metrics-detailed-analytics'] ) ) {

			$graphs[ 'Detailed Analytics' ] = array_merge(
																								$chartUsers->getGroups( 'detailed-analytics' ),
																								$chartRevenue->getGroups( 'detailed-analytics' ),
																								$chartChurn->getGroups( 'detailed-analytics' ),
																								$chartGift->getGroups( 'detailed-analytics' ),
																								$chartOrder->getGroups( 'detailed-analytics' )
																							);
		}

		if ( c::admin()->permission()->check( ['global','metrics-all','metrics-no-grouped-charts'] ) ) {
			$graphs[ 'Old Graphs' ] = array_merge(
																								$chartUsers->getGroups(),
																								$chartRevenue->getGroups(),
																								$chartChurn->getGroups(),
																								$chartGift->getGroups(),
																								$chartOrder->getGroups()
																							);
		}
		c::view()->graphs = $graphs;
		c::view()->display('charts/index');
	}

	public function cohort(){
		c::view()->cohorts = Crunchbutton_Chart_Cohort::q( 'SELECT * FROM chart_cohort ORDER BY name DESC' );
		c::view()->display( 'charts/cohort/index' );
	}

	public function cohort_new(){
		c::view()->display( 'charts/cohort/form' );
	}

	public function cohort_remove(){
		$id_chart_cohort = $this->request()[ 'id_chart_cohort' ];
		$cohort = Crunchbutton_Chart_Cohort::o( $id_chart_cohort );
		if( $cohort->id_chart_cohort ){
			$cohort->delete();
		}
		echo 'ok';
	}

}