<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public function init() {

		$this->chartId = c::getPagePiece(2);

		$this->title = c::getPagePiece(3);

		$this->number = c::getPagePiece(4);

		$this->chart = new Crunchbutton_Chart;

		switch ( $this->chartId ) {

			/* Users */
			case 'users-reclaimed-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->reclaimedByWeek( true ) );
				break;

			case 'users-active-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByWeek( true ) );
				break;

			case 'users-new-per-day':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByDay( true ) );
				break;

			case 'users-new-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByWeek( true ) );
				break;

			case 'users-new-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByMonth( true ) );
				break;

			case 'users-active-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByMonth( true ) );
				break;

			case 'users-new-per-active-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByWeek( true ) );
				break;

			case 'users-new-per-active-users-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByMonth( true ) );
				break;

			case 'users-unique-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByWeek( true ) );
				break;

			case 'users-unique-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByMonth( true ) );
				break;

			case 'users-new-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByWeekByCommunity( true ) );
			break;

			case 'users-active-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByWeekByCommunity( true ) );
			break;

			case 'users-new-per-active-users-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByWeekByCommunity( true ) );
				break;

			case 'users-unique-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByWeekByCommunity( true ) );
				break;

			/* Revenue */

			case 'gross-revenue-per-week':
				$chart = new Crunchbutton_Chart_Revenue();
				$this->renderColumn( $chart->byWeek( true ) );
				break;

			case 'gross-revenue-per-month':
				$chart = new Crunchbutton_Chart_Revenue();
				$this->renderColumn( $chart->byMonth( true ) );
				break;

			/* Orders */

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

			case 'orders-by-weekday-by-community':
				$order = new Crunchbutton_Chart_Order();
				$this->renderArea( $order->byWeekdayByCommunity( true ) );
				break;

			case 'orders-repeat-per-active-user':
				$order = new Crunchbutton_Chart_Order();
				$this->renderColumn( $order->repeatByActiveuserByWeek( true ) );
				break;

			/* Churn */

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
			
			/* Others */
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
						'to_day' => $this->chart->to_day,
						'from_day' => $this->chart->from_day,
						'months' => $months,
						'number' => $this->number,
						'unit' => $params[ 'unit' ] ,
						'totalWeeks' => $this->chart->totalWeeks(),
						'totalMonths' => $this->chart->totalMonths(),
						'totalDays' => $this->chart->totalDays(),
					]]); 
	}
}