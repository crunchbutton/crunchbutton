<?php

class Controller_api_drivers extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		if (!c::admin()->permission()->check(['global','drivers-working-hours','drivers-all'])) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		
		switch ( c::getPagePiece( 2 ) ) {
			case 'hours':
				die('#5430 deprecated');
				/*
				switch ( c::getPagePiece( 3 ) ) {
					case 'add':
						$this->addHours();
						break;
					case 'edit':
						$this->editHours();
						break;
					case 'copy':
						$this->copyHours();
						echo json_encode( array( 'success' => true ) );
						break;
					break;
					case 'copy-all':
						$this->copyAllHours();
						echo json_encode( array( 'success' => true ) );
						break;
					case 'remove':
						$this->removeHours();
						break;
					default:
						echo json_encode( [ 'error' => 'invalid object' ] );
						break;
				}
				*/
				break;
			case 'assign':
				switch ( c::getPagePiece( 3 ) ) {
					case 'order':
						$this->assignOrder();
						break;
					default:
						echo json_encode( [ 'error' => 'invalid object' ] );
						break;	
				}
				break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
				break;
		}
	}

	public function assignOrder(){
		$id_order = c::getPagePiece( 4 );
		$id_admin = $this->request()[ 'id_admin' ];
		$actions = $this->request()[ 'actions' ];

		$order = Order::o( $id_order );
		if( !$order->id_order ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		$admin = Admin::o( $id_admin );
		if( !$admin->id_admin ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		if( count( $actions ) > 0 ){
			foreach( $actions as $action ){
				$order->setStatus($action, false, $admin);
			}
		}
		echo json_encode( array( 'success' => true ) );
	}

	/*
	public function copyAllHours(){
		$week = $this->request()[ 'week' ];
		$year = $this->request()[ 'year' ];
		$admins = Admin::q( 'SELECT * FROM admin' );
		if( !$week || !$year ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		foreach( $admins as $admin ){
			$this->copyHours( $admin->id_admin, $week, $year );
		}
	}

	public function copyHours( $id_admin = false, $week = false, $year = false ){

		$id_admin = ( $id_admin ) ? $id_admin : $this->request()[ 'id_admin' ];
		$week = ( $week ) ? $week : $this->request()[ 'week' ];
		$year = ( $year ) ? $year : $this->request()[ 'year' ];

		if( !$id_admin || !$week || !$year ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		if( $week < 10 ){
			$week = '0' . intval( $week );
		}

		$admin = Admin::o( $id_admin );

		$hoursToBeCopied = [];

		$_days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days = [];
			$date = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . $i ) ), new DateTimeZone( 'GMT'  ) );;
			$days[ 'actual' ] = $date->format( 'Y-m-d' );
			$date->modify( '-7 days' );
			$days[ 'prev' ] = $date->format( 'Y-m-d' );
			$_days[] = $days;
		}

		foreach ( $_days as $days ) {
			$actual = $days[ 'actual' ];
			$prev = $days[ 'prev' ];
			$segments = Crunchbutton_Admin_Hour::segmentsByDate( $prev, $_join = ', ', $id_admin );
			Crunchbutton_Admin_Hour::removeByDateIdAdmin( $actual, $id_admin );
			if( count( $segments ) > 0 ){
				$segments = $segments[ $id_admin ][ 'hours' ];
				$segments = explode( ',',  $segments );
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $actual . ' 00:00:00', new DateTimeZone( $admin->timezone ) );
				foreach ( $segments as $segment ) {
					$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segment );
					if( $_hours ){
						$hoursToBeCopied[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];	
					}
				}
			}
		}

		foreach( $hoursToBeCopied as $hour ){
			$admin_hour = new Crunchbutton_Admin_Hour();
			$admin_hour->id_admin = $id_admin;
			$admin_hour->id_admin_created = c::admin()->id_admin;
			$admin_hour->date_start = $hour[ 'start' ];
			$admin_hour->date_end = $hour[ 'end' ];
			if( $admin_hour->date_start && $admin_hour->date_end ){
				$admin_hour->save();	
			}
		}
	}

	public function removeHours(){
		$id_admin = $this->request()[ 'id_admin' ];
		$day = $this->request()[ 'day' ];
		$month = $this->request()[ 'month' ];
		$year = $this->request()[ 'year' ];
		$date = $year . '-' . $month . '-' . $day;
		if( !$id_admin || !$date ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		Log::debug( [ 'action' => 'admin hours removed', 'id_admin' => $id_admin, 'date' => $date, 'id_admin_edit' => c::admin()->id_admin, 'type' => 'admin-hours' ] );
		Crunchbutton_Admin_Hour::removeByDateIdAdmin( $date, $id_admin );
		echo json_encode( array( 'success' => true ) );
	}

	public function editHours(){
		$id_admin = $this->request()[ 'id_admin' ];
		$day = $this->request()[ 'day' ];
		$month = $this->request()[ 'month' ];
		$year = $this->request()[ 'year' ];
		$date = $year . '-' . $month . '-' . $day;
		if( !$id_admin || !$day || !$month || !$year ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		Log::debug( [ 'action' => 'admin hours edited', 'id_admin' => $id_admin, 'date' => $date, 'id_admin_edit' => c::admin()->id_admin, 'type' => 'admin-hours' ] );
		Crunchbutton_Admin_Hour::removeByDateIdAdmin( $date, $id_admin );
		$this->addHours();
	}

	public function addHours(){
		$id_admin = $this->request()[ 'id_admin' ];
		$day = $this->request()[ 'day' ];
		$month = $this->request()[ 'month' ];
		$year = $this->request()[ 'year' ];
		$week = $this->request()[ 'week' ];
		$segments = $this->request()[ 'hours' ];
		$weekdays = $this->request()[ 'weekdays' ] ;

		if( !$id_admin || !$day || !$month || !$year ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		$admin = Admin::o( $id_admin );
	
		$hours = [];
		$segments = explode( ',',  $segments );

		if( count( $weekdays ) > 0 ){
			foreach( $weekdays as $weekday ){
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $weekday . ' 00:00:00', new DateTimeZone( $admin->timezone ) );
				foreach ( $segments as $segment ) {
					$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segment );
					if( $_hours ){
						$hours[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];	
					}
				}
			}
		} else {
			// add just the hour for the day
			$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $year . '-' . $month . '-' . $day . ' 00:00:00', new DateTimeZone( $admin->timezone ) );
			foreach ( $segments as $segment ) {
				$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segment );
				if( $_hours ){
					$hours[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];	
				}
			}
		}

		foreach( $hours as $hour ){
			$admin_hour = new Crunchbutton_Admin_Hour();
			$admin_hour->id_admin = $id_admin;
			$admin_hour->id_admin_created = c::admin()->id_admin;
			$admin_hour->date_start = $hour[ 'start' ];
			$admin_hour->date_end = $hour[ 'end' ];
			if( $admin_hour->date_start && $admin_hour->date_end ){
				$admin_hour->save();	
			}
		}
		echo json_encode( array( 'success' => true ) );
	}
	*/
}
