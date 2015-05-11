<?php

class Controller_scripts_restaurantcommunity extends Crunchbutton_Controller_Account {
	public function init() {
		echo '<pre>';

		$this->m( 'Getting restaurants:' );
		$restaurants = Crunchbutton_Restaurant::q( 'SELECT * FROM restaurant ORDER BY community ASC' );
		foreach ( $restaurants as $restaurant ) {
			$restaurant->removeCommunity();
			$id_community = $this->id_community( $restaurant->community );
			if( $id_community ){
				$this->m( $restaurant->community . ': ' . $id_community );	
				$restaurantCommunity = Crunchbutton_Restaurant_Community::q( 'SELECT * FROM restaurant_community WHERE id_restaurant = ' . $restaurant->id_restaurant . ' AND id_community = ' . $id_community );
				if( !$restaurantCommunity->id_restaurant_community ){
					$restaurantCommunity = new Crunchbutton_Restaurant_Community();	
					$restaurantCommunity->id_restaurant = $restaurant->id_restaurant;
					$restaurantCommunity->id_community = $id_community;
					$restaurantCommunity->save();
				}
			}
		}
		
		$this->addDriverGroup();
		$this->addTimezone();
	}

	public function addTimezone(){
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community' );
		foreach( $communities as $community ){
			$restaurant = Crunchbutton_Restaurant::q( 'SELECT r.* FROM restaurant r INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = ? WHERE r.active = true LIMIT 1', [$community->id_community]);
			if( $restaurant->id_restaurant ){
				$community->timezone = $restaurant->timezone;
				$community->save();
			}
		}
	}

	public function addDriverGroup(){
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community' );
		foreach( $communities as $community ){
			$this->m( $community->driverGroup() );
		}
	}

	public function id_community( $name ){
		if( $name && trim( $name ) != '' ){
			$community = Crunchbutton_Community::q( 'SELECT * FROM community WHERE name = "' . $name . '"' );

			if( $community->id_community ){
				$community->active = 1;
				$community->save();
				return $community->id_community;
			} else {
				$community = new Crunchbutton_Community();
				$community->name = $name;
				$community->active = 0;
				$community->save();
				return $community->id_community;
			}
		}
	}

	public function m( $message ){
		echo $message;
		echo "\n";
	}

}