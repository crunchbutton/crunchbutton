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
																'churn-rate-per-active-user-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'activeByWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'activeByWeekByCommunity' ) ) ),
																'churn-rate-per-active-user-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'activeByMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'activeByMonthByCommunity' ) ) ),
															)
												),
										);

	public function __construct() {
		parent::__construct();
	}

	public function activeByDay( $render = false ){
		
		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			$lost = ( ( $activePrev + $new ) - $active );
			$lost = ( $lost < 0 )	? 0 : $lost;

			// Formula: so, divide the number lost by the previous day's total
			if( $activePrev != 0 && $lost != 0 ){
				$result = $lost / $activePrev;	
			} else {
				$result = 0;
			}
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function activeByMonthByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByMonthByCommunity();
		$newUsers = $user->newByMonthByCommunity();

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

		$activeUsers = $user->activeByMonth();
		$newUsers = $user->newByMonth();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			$lost = ( ( $activePrev + $new ) - $active );
			$lost = ( $lost < 0 )	? 0 : $lost;

			// Formula: so, divide the number lost by the previous month's total
			if( $activePrev != 0 && $lost != 0 ){
				$result = $lost / $activePrev;	
			} else {
				$result = 0;
			}
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function activeByWeek( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByWeek();
		$newUsers = $user->newByWeek();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			$lost = ( ( $activePrev + $new ) - $active );
			$lost = ( $lost < 0 )	? 0 : $lost;

			// Formula: so, divide the number lost by the previous week's total
			if( $activePrev != 0 && $lost != 0 ){
				$result = $lost / $activePrev;	
			} else {
				$result = 0;
			}
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function activeByWeekByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByWeekByCommunity();
		$newUsers = $user->newByWeekByCommunity();

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

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByWeek();
		$newUsers = $user->newByWeek();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			$churn = ( ( $activePrev + $new ) - $active );
			// Do not show the negatives
			$churn = ( $churn < 0 )	? 0 : $churn;
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function byDay( $render = false ){
		
		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			$churn = ( ( $activePrev + $new ) - $active );
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
		
		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByMonth();
		$newUsers = $user->newByMonth();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			$churn = ( ( $activePrev + $new ) - $active );
			// Do not show the negatives
			$churn = ( $churn < 0 )	? 0 : $churn;

			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}
}