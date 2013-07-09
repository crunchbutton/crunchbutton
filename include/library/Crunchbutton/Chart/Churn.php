<?php 

class Crunchbutton_Chart_Churn extends Crunchbutton_Chart {
	
	public $unit = 'users';
	public $description = 'Users';

	public $group = 'group1';

	public $groups = array( 
													'group1' => array( 
																						'churn-rate-per-week' => 'Churn Rate per Week',
																						'churn-rate-per-month' => 'Churn Rate per Month',
																						'churn-rate-per-active-user-per-week' => 'Churn Rate per Active User per Week',
																						'churn-rate-per-active-user-per-month' => 'Churn Rate per Active User per Month'
																						
																			) 
										);

	public function __construct() {
		parent::__construct();
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

			// Formula: so, divide the number lost by the previous week's total
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