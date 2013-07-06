<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public function init() {

		$this->chartId = c::getPagePiece(2);

		$this->divId = c::getPagePiece(3);

		$this->chart = new Crunchbutton_Chart;

		switch ( $this->chartId ) {

			/* Users */
			case 'users-reclaimed-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->reclaimedByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'users-active-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'users-new-per-day':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByDay( true ), $chart->getGroupedCharts() );
				break;

			case 'users-new-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'users-new-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'users-active-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'users-new-per-active-users-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'users-new-per-active-users-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'users-unique-per-week':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'users-unique-per-month':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'users-new-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newByWeekByCommunity( true ), $chart->getGroupedCharts() );
			break;

			case 'users-active-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->activeByWeekByCommunity( true ), $chart->getGroupedCharts() );
			break;

			case 'users-new-per-active-users-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->newPerActiveByWeekByCommunity( true ), $chart->getGroupedCharts() );
				break;

			case 'users-unique-per-week-by-community':
				$chart = new Crunchbutton_Chart_User();
				$this->renderColumn( $chart->uniqueByWeekByCommunity( true ), $chart->getGroupedCharts() );
				break;

			/* Revenue */

			case 'gross-revenue-per-week':
				$chart = new Crunchbutton_Chart_Revenue();
				$this->renderColumn( $chart->byWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'gross-revenue-per-month':
				$chart = new Crunchbutton_Chart_Revenue();
				$this->renderColumn( $chart->byMonth( true ), $chart->getGroupedCharts() );
				break;

			/* Orders */

			case 'orders-by-user-per-week':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byUsersPerWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'orders-by-user-per-month':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byUsersPerMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'orders-per-week':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'orders-per-month':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'orders-per-week-by-community':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->byWeekPerCommunity( true ), $chart->getGroupedCharts() );
				break;

			case 'orders-by-weekday-by-community':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderArea( $chart->byWeekdayByCommunity( true ), $chart->getGroupedCharts() );
				break;

			case 'orders-repeat-per-active-user':
				$chart = new Crunchbutton_Chart_Order();
				$this->renderColumn( $chart->repeatByActiveuserByWeek( true ), $chart->getGroupedCharts() );
				break;

			/* Churn */

			case 'churn-rate-per-active-user-per-month':
				$chart = new Crunchbutton_Chart_Churn();
				$this->renderColumn( $chart->activeByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'churn-rate-per-active-user-per-week':
				$chart = new Crunchbutton_Chart_Churn();
				$this->renderColumn( $chart->activeByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'churn-rate-per-week':
				$chart = new Crunchbutton_Chart_Churn();
				$this->renderColumn( $chart->byWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'churn-rate-per-month':
				$chart = new Crunchbutton_Chart_Churn();
				$this->renderColumn( $chart->byMonth( true ), $chart->getGroupedCharts() );
				break;
			
			/* Gift card */

			case 'gift-cards-created-per-day':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->createdByDay( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-created-per-week':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->createdByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-created-per-month':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->createdByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-redeemed-per-day':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->redeemedByDay( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-redeemed-per-week':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->redeemedByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-redeemed-per-month':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->redeemedByMonth( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-redeemed-per-group-per-day':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->redeemedPerGroupByDay( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-redeemed-per-group-per-week':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->redeemedPerGroupByWeek( true ), $chart->getGroupedCharts() );
				break;

			case 'gift-cards-redeemed-per-group-per-month':
				$chart = new Crunchbutton_Chart_Giftcard();
				$this->renderColumn( $chart->redeemedPerGroupByMonth( true ), $chart->getGroupedCharts() );
				break;

			/* Others */

			case 'weeks':
				echo $this->chart->weeksToJson();
				break;
			default:
			break;
		}
	}

	private function renderArea( $params, $groups ){
			c::view()->display('charts/area', ['set' => [
				'chartId' => $this->chartId,
				'data' => $params[ 'data' ],
				'title' => $this->title,
				'number' => $this->number,
				'unit' => $params[ 'unity' ],
				'groups' => $groups,
				'divId' => $this->divId
			]]); 
	}

	private function renderColumn( $params, $groups ){

		$interval = ( $params[ 'interval' ] ) ? $params[ 'interval' ] : 'week';

		return c::view()->display('charts/column', ['set' => [
						'chartId' => $this->chartId,
						'data' => $params[ 'data' ] ,
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
						'groups' => $groups,
						'divId' => $this->divId
					]]); 
	}
}