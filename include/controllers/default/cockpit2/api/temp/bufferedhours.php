<?php

class Controller_api_temp_bufferedhours extends Crunchbutton_Controller_Account {
	public function init() {

		$community = Community::o( c::getPagePiece( 3 ) );

		if( !$community->id_community ){
			return;
		}

		echo "<h1>{$community->name}</h1>";

		echo "<h2>Shifts</h2>";

		$community_hrs = $community->shiftsForNextWeek( true );

		// empty array to store the merged hours
		$_community_hours = [];

		// Convert the hours to a simple array
		if( $community_hrs && count( $community_hrs ) ){
			foreach ( $community_hrs as $hour ) {
				if( !isset( $_community_hours[ trim( $hour->full ) ] ) ){
					$_community_hours[ trim( $hour->full ) ] = [];
				}
				$_community_hours[ trim( $hour->full ) ][] = [ trim( $hour->time_open ), trim( $hour->time_close ) ];
			}

			uksort( $_community_hours,
			function( $a, $b ) {
				$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
				return( $weekdays[ $a ] > $weekdays[ $b ] );
			} );
		}

		echo "<table border=1>";
			echo "<tr>";
				echo "<th>Day</th>";
				echo "<th>Shifts</th>";
			echo "</tr>";

		foreach( $_community_hours as $days => $hours ){
			echo "<tr>";
				echo "<td><strong>{$days}</strong></td>";
				echo "<td>";
				foreach( $hours as $segments ){
					$start = $segments[ 0 ];
					$start = explode( ':' , $start );
					$ampm = 'am';
					if( intval( $start[ 0 ] ) > 12 ){
						$start[ 0 ] = $start[ 0 ] - 12;
						$ampm = 'pm';
					}
					echo $start[ 0 ] . ':' . $start[ 1 ] . ' ' . $ampm;
					echo " - ";
					$finish = $segments[ 1 ];
					$finish = explode( ':' , $finish );
					$ampm = 'am';
					if( intval( $finish[ 0 ] ) > 12 ){
						$finish[ 0 ] = $finish[ 0 ] - 12;
						$ampm = 'pm';
					}
					echo $finish[ 0 ] . ':' . $finish[ 1 ] . ' ' . $ampm;
					echo "<br>";

				}
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";


		echo "<h2>Restaurants</h2>";

		echo "<table border=1>";
			echo "<tr>";
				echo "<th>Restaurant</th>";
				echo "<th>Regular Hours</th>";
				echo "<th>Buffered Hours</th>";
			echo "</tr>";
		$restaurants = $community->restaurants();
		foreach( $restaurants as $restaurant ){

			if( $restaurant->delivery_service ){
				echo "<tr>";
					echo "<td>{$restaurant->name}</td>";
					echo "<td>{$restaurant->closed_message()}</td>";
					$restaurant->force_buffer = true;
					$restaurant->_hoursByRestaurant = null;
					$restaurant->_hours = null;
					echo "<td>{$restaurant->closed_message()}</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
	}
}