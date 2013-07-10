<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public function process( $info, $chart ){

		$title = $info[ 'title' ];
		$subTitle = $info['chart'][ 'title' ];
		$type = $info['chart'][ 'type' ];
		$method = $info['chart'][ 'method' ];
		
		if( $_GET['filter'] ){
			$filters = $info['chart'][ 'filters' ];
			foreach( $filters as $filter ){
				if( $filter[ 'type' ] == $_GET['filter'] ){
					$method = $filter[ 'method' ];
					$info[ 'filter' ] = $filter[ 'type' ];
				}
			}
		}

		$this->chart->processInterval( $info[ 'chart' ][ 'interval' ] );
		$chart->processInterval( $info[ 'chart' ][ 'interval' ] );

		switch ( $type ) {
			case 'column':
				$params = array_merge( $chart->$method( true ), $info );
				$this->renderColumn( $params, $chart->getGroupedCharts( $info ) );
				break;
			case 'area':
				$params = array_merge( $chart->$method( true ), $info );
				$this->renderArea( $params, $chart->getGroupedCharts( $info ) );
				break;
			case 'pie_communities':
				$params = array_merge( $chart->$method( true ), $info );
				$this->renderPieCommunities( $params, $chart->getGroupedCharts( $info ) );
				break;
		}
	}

	public function init() {

		$this->chart = new Crunchbutton_Chart;

		if( c::getPagePiece(2) == 'weeks' ){
			echo $this->chart->weeksToJson();
			exit;
		}

		if( c::getPagePiece(2) == 'cohort' ){
			$this->cohort();
			exit;
		}

		$this->chartId = c::getPagePiece(2);

		$this->divId = c::getPagePiece(3);

		// Check if it is an User chart
		$chart = new Crunchbutton_Chart_User();
		$info = $chart->getChartInfo( $this->chartId );
		if( $info ){ $this->process( $info, $chart ); exit; }

		// Check if it is a Revenue chart
		$chart = new Crunchbutton_Chart_Revenue();
		$info = $chart->getChartInfo( $this->chartId );
		if( $info ){ $this->process( $info, $chart ); exit; }

		// Check if it is a Churn chart
		$chart = new Crunchbutton_Chart_Churn();
		$info = $chart->getChartInfo( $this->chartId );
		if( $info ){ $this->process( $info, $chart ); exit; }

		// Check if it is a Gift card chart
		$chart = new Crunchbutton_Chart_Giftcard();
		$info = $chart->getChartInfo( $this->chartId );
		if( $info ){ $this->process( $info, $chart ); exit; }

		// Check if it is an Order card chart
		$chart = new Crunchbutton_Chart_Order();
		$info = $chart->getChartInfo( $this->chartId );
		if( $info ){ $this->process( $info, $chart ); exit; }



	}

	private function renderPieCommunities( $params, $groups ){
			c::view()->display('charts/pie_communities', ['set' => [
				'chartId' => $this->chartId,
				'data' => $params[ 'data' ],
				'title' => $this->title,
				'number' => $this->number,
				'unit' => $params[ 'unity' ],
				'groups' => $groups,
				'divId' => $this->divId
			]]); 
	}

	private function renderArea( $params, $groups ){

		$title = $params[ 'title' ] . ' : ' . $groups[ $this->chartId ][ 'title' ];

		$interval = ( $params[ 'interval' ] ) ? $params[ 'interval' ] : 'week';
		
		c::view()->display('charts/area', ['set' => [
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
					'title' => $title,
					'groups' => $groups,
					'info' => $params,
					'hideGroups' => $params[ 'hideGroups' ],
					'hideSlider' => $params[ 'hideSlider' ],
					'divId' => $this->divId
		]]); 
	}

	private function renderColumn( $params, $groups ){

		$title = $params[ 'title' ] . ' : ' . $groups[ $this->chartId ][ 'title' ];

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
						'title' => $title,
						'groups' => $groups,
						'info' => $params,
						'divId' => $this->divId
					]]); 
	}
}