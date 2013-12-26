<?php 

class Crunchbutton_Chart_Churn extends Crunchbutton_Chart {
	
	public $unit = 'users';
	public $description = 'Users';

	public $groups = array( 
												'group-churn-rate' => array(
														'title' => 'Churn Rate',
														'activeDays' => 60,
														'charts' => array(  
																'churn-rate-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'byDay' ),
																'churn-rate-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'byWeek'),
																'churn-rate-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'byMonth'),
															)
												),
												'group-churn-rate-per-active-user' => array(
														'title' => 'Churn Rate per Active User',
														'activeDays' => 60,
														'tags' => array( 'investors' ),
														'charts' => array(  
																'churn-rate-per-active-user-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'activeByDay'),
																'churn-rate-per-active-user-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'activeByWeek' /* , 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'activeByWeekByCommunity' ) ) */ ),
																'churn-rate-per-active-user-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'activeByMonth' /*, 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'activeByMonthByCommunity' ) ) */ ),
															)
												),
												'group-historical-churn' => array(
														'title' => 'Historical Churn',
														'activeDays' => 60,
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(  
																'historial-churn-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'historicalChurnByDay'),
															)
												),
												'group-historical-churn-rate-per-active-user' => array(
														'title' => 'Historical Churn Rate',
														'activeDays' => 60,
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(  
																'historial-churn-rate-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'historicalChurnRateByDay'),
															)
												),
										);

	public function __construct() {
		parent::__construct();
	}

	
	public function historicalChurnRateByDay( $render = false ){
		$user = new Crunchbutton_Chart_User();
		$daysForward = $this->activeUsersInterval;
		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();
		$activeLastDays = $this->activeFromLastDays();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$activeToday = $activeUsers[ $i ]->Total;
			$activeForwardDays = $activeUsers[ ( $i + $daysForward ) ]->Total;
			$activeForwardDaysPlusOne = $activeUsers[ ( $i + $daysForward + 1 ) ]->Total;
			$newForwardDays = $newUsers[ ( $i + $daysForward ) ]->Total;
			$newForwardDaysPlusOne = $newUsers[ ( $i + $daysForward +  1 ) ]->Total;
			$churn = ( ( $activeLastDays + $newForwardDaysPlusOne ) - $activeForwardDaysPlusOne ) / $activeToday;
			// $churn = ( ( $activeForwardDays + $newForwardDaysPlusOne ) - $activeForwardDaysPlusOne ) / $activeLastDays;
			// Do not show the negatives
			$churn = ( $churn < 0 )	? 0 : $churn;
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function historicalChurnByDay( $render = false ){
		$user = new Crunchbutton_Chart_User();
		$daysForward = $this->activeUsersInterval;
		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();
		$activeLastDays = $this->activeFromLastDays();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$activeForwardDays = $activeUsers[ ( $i + $daysForward ) ]->Total;
			$activeForwardDaysPlusOne = $activeUsers[ ( $i + $daysForward + 1 ) ]->Total;
			$newForwardDays = $newUsers[ ( $i + $daysForward ) ]->Total;
			$newForwardDaysPlusOne = $newUsers[ ( $i + $daysForward +  1 ) ]->Total;
			$churn = ( ( $activeLastDays + $newForwardDaysPlusOne ) - $activeForwardDaysPlusOne );
			// Do not show the negatives
			$churn = ( $churn < 0 )	? 0 : $churn;
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function activeByDay( $render = false ){
		$user = new Crunchbutton_Chart_User();
		$daysForward = $this->activeUsersInterval;
		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();
		// Formula #2251
		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$activeToday = $activeUsers[ $i ];
			$activeForwardDays = $activeUsers[ ( $i + $daysForward ) ]->Total;
			$activeForwardDaysPlusOne = $activeUsers[ ( $i + $daysForward + 1 ) ]->Total;
			$newForwardDays = $newUsers[ ( $i + $daysForward ) ]->Total;
			$newForwardDaysPlusOne = $newUsers[ ( $i + $daysForward +  1 ) ]->Total;
			$churn = ( ( $activeForwardDaysPlusOne + $newForwardDaysPlusOne ) - $activeForwardDaysPlusOne ) / $activeToday;
			// Do not show the negatives
			// $churn = ( $churn < 0 )	? 0 : $churn;
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => 'Users', 'interval' => 'day' );
		}
		return $data;
	}

	public function activeByMonthByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByMonthByCommunity();
		$newUsers = $user->newByMonthByCommunityGrouped();

		$communities = $this->allCommunities();

		$_data = [];
		$_prev = [];

		foreach ( $activeUsers as $active ) {
			if( !$_data[ $active->Label ] ){
				$_data[ $active->Label ] = [];	
			}
			$_data[ $active->Label ][ 'ActiveUser' ][ $active->Type ] = $active->Total;
			$_data[ $active->Label ][ 'ActiveUserPrev' ][ $active->Type ] = ( $_prev[ $active->Type ] ? $_prev[ $active->Type ] : 0 );
			$_prev[ $active->Type ] = $active->Total;
		}

		foreach ( $newUsers as $newUser ) {
			if( !$_data[ $newUser->Label ] ){
				$_data[ $newUser->Label ] = [];	
			}
			$_data[ $newUser->Label ][ 'NewUser' ][ $newUser->Type ] = $newUser->Total;
		}
		
		$data = [];

		foreach ( $_data as $label => $values ) {
			foreach( $communities as $community ){
				$active = $values[ 'ActiveUser' ][ $community ];
				$newUser = $values[ 'NewUser' ][ $community ];
				$prev = $values[ 'ActiveUserPrev' ][ $community ];

				$lost = ( ( $prev + $newUser ) - $active );
				$lost = ( $lost < 0 )	? 0 : $lost;

				// Formula: so, divide the number lost by the previous week's total
				if( $prev != 0 && $lost != 0 ){
					$result = $lost / $prev;	
				} else {
					$result = 0;
				}

				$data[] = ( object ) array( 'Label' => $label, 'Total' => $result, 'Type' => $community ); 
			}
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function activeByMonth( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$daysForward = $this->activeUsersInterval;
		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();
		// Formula #2251
		$_data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$activeToday = $activeUsers[ $i ];
			$activeForwardDays = $activeUsers[ ( $i + $daysForward ) ]->Total;
			$activeForwardDaysPlusOne = $activeUsers[ ( $i + $daysForward + 1 ) ]->Total;
			$newForwardDays = $newUsers[ ( $i + $daysForward ) ]->Total;
			$newForwardDaysPlusOne = $newUsers[ ( $i + $daysForward +  1 ) ]->Total;
			$_data[] = array( 'activeToday' => $activeToday, 'activeForwardDays' => $activeForwardDays, 'activeForwardDaysPlusOne' => $activeForwardDaysPlusOne, 'newForwardDays' => $newForwardDays, 'newForwardDaysPlusOne' => $newForwardDaysPlusOne );
		}
		$allDays = $this->allDays();
		$days = [];
		$months = [];
		$data = [];

		$byDay = $this->activeByDay();

		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$days[] = $allDays[ $i ];
		}

		for( $i = 0; $i < count( $byDay ); $i++ ){
			$month = $this->dateToMonth( $days[ $i ] );
			if( !$months[ $month ] ){
				
				$months[ $month ] = array( 	'Label' => $month,
																	'activeToday' => $_data[ $i ][ 'activeToday' ], 
																	'activeForwardDays' => $_data[ $i ][ 'activeForwardDays' ], 
																	'activeForwardDaysPlusOne' => $_data[ $i ][ 'activeForwardDaysPlusOne' ], 
																	'newForwardDays' => $_data[ $i ][ 'newForwardDays' ], 
																	'newForwardDaysPlusOne' => $_data[ $i ][ 'newForwardDaysPlusOne' ]
																);
			} else {
				$months[ $month ][ 'activeToday' ] = $months[ $month ][ 'activeToday' ] + $_data[ $i ][ 'activeToday' ];
				$months[ $month ][ 'activeForwardDays' ] = $months[ $month ][ 'activeForwardDays' ] + $_data[ $i ][ 'activeForwardDays' ];
				$months[ $month ][ 'activeForwardDaysPlusOne' ] = $months[ $month ][ 'activeForwardDaysPlusOne' ] + $_data[ $i ][ 'activeForwardDaysPlusOne' ];
				$months[ $month ][ 'newForwardDays' ] = $months[ $month ][ 'newForwardDays' ] + $_data[ $i ][ 'newForwardDays' ];
				$months[ $month ][ 'newForwardDaysPlusOne' ] = $months[ $month ][ 'newForwardDaysPlusOne' ] + $_data[ $i ][ 'newForwardDaysPlusOne' ];
			}
		}

		foreach( $months as $month ){
			$churn = ( ( $month[ 'activeForwardDaysPlusOne' ] + $month[ 'newForwardDaysPlusOne' ] ) - $month[ 'activeForwardDaysPlusOne' ] ) / $month[ 'activeToday' ];
			$data[] = ( object ) array( 'Label' => $month[ 'Label' ] , 'Total' => $churn, 'Type' => '%' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function activeByWeek( $render = false ){		
		$user = new Crunchbutton_Chart_User();
		$daysForward = $this->activeUsersInterval;
		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();
		// Formula #2251
		$_data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$activeToday = $activeUsers[ $i ];
			$activeForwardDays = $activeUsers[ ( $i + $daysForward ) ]->Total;
			$activeForwardDaysPlusOne = $activeUsers[ ( $i + $daysForward + 1 ) ]->Total;
			$newForwardDays = $newUsers[ ( $i + $daysForward ) ]->Total;
			$newForwardDaysPlusOne = $newUsers[ ( $i + $daysForward +  1 ) ]->Total;
			$_data[] = array( 'activeToday' => $activeToday, 'activeForwardDays' => $activeForwardDays, 'activeForwardDaysPlusOne' => $activeForwardDaysPlusOne, 'newForwardDays' => $newForwardDays, 'newForwardDaysPlusOne' => $newForwardDaysPlusOne );
		}
		$allDays = $this->allDays();
		$days = [];
		$weeks = [];
		$data = [];

		$byDay = $this->activeByDay();

		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$days[] = $allDays[ $i ];
		}

		for( $i = 0; $i < count( $byDay ); $i++ ){
			$week = $this->dateToWeek( $days[ $i ] );
			if( !$weeks[ $week ] ){
				
				$weeks[ $week ] = array( 	'Label' => $week,
																	'activeToday' => $_data[ $i ][ 'activeToday' ], 
																	'activeForwardDays' => $_data[ $i ][ 'activeForwardDays' ], 
																	'activeForwardDaysPlusOne' => $_data[ $i ][ 'activeForwardDaysPlusOne' ], 
																	'newForwardDays' => $_data[ $i ][ 'newForwardDays' ], 
																	'newForwardDaysPlusOne' => $_data[ $i ][ 'newForwardDaysPlusOne' ]
																);
			} else {
				$weeks[ $week ][ 'activeToday' ] = $weeks[ $week ][ 'activeToday' ] + $_data[ $i ][ 'activeToday' ];
				$weeks[ $week ][ 'activeForwardDays' ] = $weeks[ $week ][ 'activeForwardDays' ] + $_data[ $i ][ 'activeForwardDays' ];
				$weeks[ $week ][ 'activeForwardDaysPlusOne' ] = $weeks[ $week ][ 'activeForwardDaysPlusOne' ] + $_data[ $i ][ 'activeForwardDaysPlusOne' ];
				$weeks[ $week ][ 'newForwardDays' ] = $weeks[ $week ][ 'newForwardDays' ] + $_data[ $i ][ 'newForwardDays' ];
				$weeks[ $week ][ 'newForwardDaysPlusOne' ] = $weeks[ $week ][ 'newForwardDaysPlusOne' ] + $_data[ $i ][ 'newForwardDaysPlusOne' ];
			}
		}

		foreach( $weeks as $week ){
			$churn = ( ( $week[ 'activeForwardDaysPlusOne' ] + $week[ 'newForwardDaysPlusOne' ] ) - $week[ 'activeForwardDaysPlusOne' ] ) / $week[ 'activeToday' ];
			$data[] = ( object ) array( 'Label' => $week[ 'Label' ] , 'Total' => $churn, 'Type' => '%' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
		
	}

	public function activeByWeekByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByWeekByCommunity();
		$newUsers = $user->newByWeekByCommunityGrouped();

		$communities = $this->allCommunities();

		$_data = [];
		$_prev = [];

		foreach ( $activeUsers as $active ) {
			if( !$_data[ $active->Label ] ){
				$_data[ $active->Label ] = [];	
			}
			$_data[ $active->Label ][ 'ActiveUser' ][ $active->Type ] = $active->Total;
			$_data[ $active->Label ][ 'ActiveUserPrev' ][ $active->Type ] = ( $_prev[ $active->Type ] ? $_prev[ $active->Type ] : 0 );
			$_prev[ $active->Type ] = $active->Total;
		}

		foreach ( $newUsers as $newUser ) {
			if( !$_data[ $newUser->Label ] ){
				$_data[ $newUser->Label ] = [];	
			}
			$_data[ $newUser->Label ][ 'NewUser' ][ $newUser->Type ] = $newUser->Total;
		}
		
		$data = [];

		foreach ( $_data as $label => $values ) {
			foreach( $communities as $community ){
				$active = $values[ 'ActiveUser' ][ $community ];
				$newUser = $values[ 'NewUser' ][ $community ];
				$prev = $values[ 'ActiveUserPrev' ][ $community ];

				$lost = ( ( $prev + $newUser ) - $active );
				$lost = ( $lost < 0 )	? 0 : $lost;

				// Formula: so, divide the number lost by the previous week's total
				if( $prev != 0 && $lost != 0 ){
					$result = $lost / $prev;	
				} else {
					$result = 0;
				}

				$data[] = ( object ) array( 'Label' => $label, 'Total' => $result, 'Type' => $community ); 
			}
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function byWeek( $render = false ){

		$allDays = $this->allDays();
		$days = [];
		$weeks = [];
		$data = [];

		$byDay = $this->byDay();

		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$days[] = $allDays[ $i ];
		}

		for( $i = 0; $i < count( $byDay ); $i++ ){
			$week = $this->dateToWeek( $days[ $i ] );
			if( !$weeks[ $week ] ){
				$weeks[ $week ] = array( 'Label' => $week, 'Total' => $byDay[ $i ]->Total );
			} else {
				$weeks[ $week ][ 'Total' ] = $weeks[ $week ][ 'Total' ] + $byDay[ $i ]->Total;
			}
		}

		foreach( $weeks as $week ){
			$data[] = ( object ) array( 'Label' => $week[ 'Label' ] , 'Total' => $week[ 'Total' ], 'Type' => 'Users' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function byDay( $render = false ){
		
		$user = new Crunchbutton_Chart_User();
		$daysForward = $this->activeUsersInterval;
		$activeUsers = $user->activeByDay();
		// Formula #2251
		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$activeForwardDays = $activeUsers[ ( $i + $daysForward ) ]->Total;
			$activeForwardDaysPlusOne = $activeUsers[ ( $i + $daysForward + 1 ) ]->Total;
			$newForwardDays = $newUsers[ ( $i + $daysForward ) ]->Total;
			$newForwardDaysPlusOne = $newUsers[ ( $i + $daysForward +  1 ) ]->Total;
			$churn = ( ( $activeForwardDaysPlusOne + $newForwardDaysPlusOne ) - $activeForwardDaysPlusOne );
			// Do not show the negatives
			$churn = ( $churn < 0 )	? 0 : $churn;
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function byMonth( $render = false ){

		$allDays = $this->allDays();
		$days = [];
		$months = [];
		$data = [];

		$byDay = $this->byDay();

		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$days[] = $allDays[ $i ];
		}

		for( $i = 0; $i < count( $byDay ); $i++ ){
			$month = $this->dateToMonth( $days[ $i ], true );
			if( !$months[ $month ] ){
				$months[ $month ] = array( 'Label' => $month, 'Total' => $byDay[ $i ]->Total );
			} else {
				$months[ $month ][ 'Total' ] = $months[ $month ][ 'Total' ] + $byDay[ $i ]->Total;
			}
		}

		foreach( $months as $month ){
			$data[] = ( object ) array( 'Label' => $month[ 'Label' ] , 'Total' => $month[ 'Total' ], 'Type' => 'Users' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}
}