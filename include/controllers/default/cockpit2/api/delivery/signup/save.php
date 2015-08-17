<?php

class Controller_api_delivery_signup_save extends Crunchbutton_Controller_Rest {

	public function init() {

		$name = $this->request()[ 'name' ];
		$email = $this->request()[ 'email' ];
		$university = $this->request()[ 'university' ];
		$city = $this->request()[ 'city' ];
		$state = $this->request()[ 'state' ];
		$restaurants = $this->request()[ 'restaurants' ];

		$error = [ 'required' => [] ];

		if( !$name ){
			$error['required'][] = 'name';
		}
		if( !$email ){
			$error['required'][] = 'email';
		}
		if( !$city ){
			$error['required'][] = 'town';
		}
		if( !$state ){
			$error['required'][] = 'state';
		}

		if( $restaurants ){
			$restaurants = join( ', ', $restaurants );
		}

		if( count( $error['required'] ) ){
			echo json_encode( [ 'error' => 'Required fields: ' . join( ', ', $error['required'] ) ] );exit;;
			exit;
		}

		$signup = new Crunchbutton_Delivery_Signup;
		$signup->name = $name;
		$signup->email = $email;
		$signup->university = $university;
		$signup->city = $city;
		$signup->state = $state;
		$signup->restaurants = $restaurants;
		$signup->status = Crunchbutton_Delivery_Signup::STATUS_NEW;
		$signup->date = date( 'Y-m-d H:i:s' );
		$signup->save();

		echo json_encode( [ 'success' => true ] );exit;

	}
}
