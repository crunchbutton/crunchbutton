<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public function init() {

		$this->chartId = c::getPagePiece(2);

		$this->title = c::getPagePiece(3);

		$this->number = c::getPagePiece(4);

		$this->chart = new Crunchbutton_Chart;

		$query = '';
		$union = '';

		switch ( $this->chartId ) {

			case 'new-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByWeek( true ) );
				break;

			case 'new-users-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByMonth( true ) );
				break;

			case 'gross-revenue-per-week':
				$chart = new Crunchbutton_Chart_Revenue();
				$this->renderColumn( $chart->byWeek( true ) );
				break;

			case 'gross-revenue-per-month':
				$chart = new Crunchbutton_Chart_Revenue();
				$this->renderColumn( $chart->byMonth( true ) );
				break;

			case 'active-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByWeek( true ) );
				break;

			case 'active-users-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByMonth( true ) );
				break;

			case 'new-users-per-active-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByWeek( true ) );
				break;

			case 'new-users-per-active-users-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByMonth( true ) );
				break;

			case 'unique-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByWeek( true ) );
				break;

			case 'unique-users-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByMonth( true ) );
				break;

			case 'orders-by-user-per-week':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byUsersPerWeek( true ) );
				break;

			case 'orders-by-user-per-month':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byUsersPerMonth( true ) );
				break;

			case 'orders-per-week':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byWeek( true ) );
				break;

			case 'orders-per-month':
				$order = new Crunchbutton_Chart_Order();
				$this->renderColumn( $order->byMonth( true ) );
				break;

			case 'orders-per-week-by-community':
				$order = new Crunchbutton_Chart_Order();
				$this->renderColumn( $order->byWeekPerCommunity( true ) );
				break;

			case 'new-users-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByWeekByCommunity( true ) );
			break;

			case 'active-users-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByWeekByCommunity( true ) );
			break;

			case 'new-users-per-active-users-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByWeekByCommunity( true ) );
				break;

			case 'unique-users-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByWeekByCommunity( true ) );
				break;

			case 'orders-by-weekday-by-community':
				$order = new Crunchbutton_Chart_Order();
				$this->renderArea( $order->byWeekdayByCommunity( true ) );
				break;

			case 'repeat-orders-per-active-user':
				$order = new Crunchbutton_Chart_Order();
				$this->renderColumn( $order->repeatByActiveuserByWeek( true ) );
				break;

			case 'reclaimed-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->reclaimedByWeek( true ) );
				break;

			case 'churn-rate-per-active-user-per-month':
			$chart = new Crunchbutton_Chart_Churn();
			$this->renderColumn( $chart->activeByMonth( true ) );
			break;

			case 'churn-rate-per-active-user-per-week':
			$chart = new Crunchbutton_Chart_Churn();
			$this->renderColumn( $chart->activeByWeek( true ) );
			break;

			case 'churn-rate-per-week':
			$chart = new Crunchbutton_Chart_Churn();
			$this->renderColumn( $chart->byWeek( true ) );
			break;

			case 'churn-rate-per-month':
			$chart = new Crunchbutton_Chart_Churn();
			$this->renderColumn( $chart->byMonth( true ) );
			break;
			
			case 'weeks':
				echo $this->chart->weeksToJson();
				break;
			default:
			break;
		}
	}

	private function renderArea( $params ){
			c::view()->display('charts/area', ['set' => [
				'chartId' => $this->chartId,
				'data' => $params[ 'data' ],
				'title' => $this->title,
				'number' => $this->number,
				'unit' => $params[ 'unity' ],
			]]); 
	}

	private function renderColumn( $params ){

		$interval = ( $params[ 'interval' ] ) ? $params[ 'interval' ] : 'week';

		return c::view()->display('charts/column', ['set' => [
						'chartId' => $this->chartId,
						'data' => $params[ 'data' ] ,
						'title' => $this->title,
						'interval' => $interval,
						'to' => $this->chart->to,
						'from' => $this->chart->from,
						'to_month' => $this->chart->to_month,
						'from_month' => $this->chart->from_month,
						'months' => $months,
						'number' => $this->number,
						'unit' => $params[ 'unit' ] ,
						'totalWeeks' => $this->chart->totalWeeks(),
						'totalMonths' => $this->chart->totalMonths()
					]]); 
	}
}