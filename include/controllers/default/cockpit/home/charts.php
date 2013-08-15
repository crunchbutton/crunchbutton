<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public function process( $info, $chart ){
		$title = $info[ 'title' ];
		$subTitle = $info['chart'][ 'title' ];
		$type = $info['chart'][ 'type' ];
		$method = $info['chart'][ 'method' ];
		
		if( $_GET['filter'] ){
			$filters = $info['chart'][ 'filters' ];
			if( $filters ){
				foreach( $filters as $filter ){
					if( $filter[ 'type' ] == $_GET['filter'] ){
						$method = $filter[ 'method' ];
						$info[ 'filter' ] = $filter[ 'type' ];
					}
				}
			}
		}

		if( $_GET[ 'cohort_type' ] ){
			$info[ 'cohort_type' ] = $_GET[ 'cohort_type' ];
		} 

		if( $_GET[ 'id_chart_cohort' ] ){
			$info[ 'id_chart_cohort' ] = $_GET[ 'id_chart_cohort' ];
		}

		$info[ '_filter' ] = "&filter={$info[ 'filter' ]}&cohort_type={$info[ 'cohort_type' ]}&id_chart_cohort={$info[ 'id_chart_cohort' ]}";

		$this->chart->processInterval( $info[ 'chart' ][ 'interval' ] );
		$chart->processInterval( $info[ 'chart' ][ 'interval' ] );

		$description = $this->chart->getChartDescription( $this->chartId );
		$title = $this->chart->getChartTitle( $this->chartId );

		switch ( $type ) {
			case 'column':
				$params = array_merge( $chart->$method( true ), $info );
				$this->renderColumn( $params, $chart->getGroupedCharts( $info ), $description, $title );
				break;
			case 'area':
				$params = array_merge( $chart->$method( true ), $info );
				$this->renderArea( $params, $chart->getGroupedCharts( $info ), $description, $title );
				break;
			case 'pie_communities':
				$params = array_merge( $chart->$method( true ), $info );
				$this->renderPieCommunities( $params, $chart->getGroupedCharts( $info ), $description, $title );
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

	private function renderPieCommunities( $params, $groups, $description, $title ){
			
			$subtitle = $params[ 'title' ] . ' : ' . $groups[ $this->chartId ][ 'title' ];

			if( !$title ){
				$title = $subtitle;
				$subtitle = '';
			}

			c::view()->display('charts/pie_communities', ['set' => [
				'chartId' => $this->chartId,
				'data' => $params[ 'data' ],
				'title' => $this->title,
				'number' => $this->number,
				'unit' => $params[ 'unity' ],
				'groups' => $groups,
				'divId' => $this->divId,
				'description' => $description
			]]); 
	}

	private function renderArea( $params, $groups, $description, $title ){

		$subtitle = $params[ 'title' ] . ' : ' . $groups[ $this->chartId ][ 'title' ];

		if( !$title ){
			$title = $subtitle;
			$subtitle = '';
		}

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
					'subtitle' => $subtitle,
					'groups' => $groups,
					'info' => $params,
					'hideGroups' => $params[ 'hideGroups' ],
					'hideSlider' => $params[ 'hideSlider' ],
					'divId' => $this->divId,
					'description' => $description
		]]); 
	}

	private function renderColumn( $params, $groups, $description, $title ){

		$subtitle = $params[ 'title' ] . ' : ' . $groups[ $this->chartId ][ 'title' ];

		if( !$title ){
			$title = $subtitle;
			$subtitle = '';
		}

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
						'subtitle' => $subtitle,
						'groups' => $groups,
						'info' => $params,
						'divId' => $this->divId,
						'description' => $description
					]]); 
	}
}