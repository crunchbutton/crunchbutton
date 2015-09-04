<?php

class Controller_api_delivery_signup extends Crunchbutton_Controller_Rest {

	public function init() {

		if( c::getPagePiece( 3 ) == 'restaurants' ){
			$restaurants = [];
			$restaurants[ 'Taco Bell' ] = [ 'name' => 'Taco Bell', 'checked' => false ];
			$restaurants[ 'McDonalds' ] = [ 'name' => 'McDonalds', 'checked' => false ];
			$restaurants[ 'Burger King' ] = [ 'name' => 'Burger King', 'checked' => false ];
			$restaurants[ 'In-N-Out' ] = [ 'name' => 'In-N-Out', 'checked' => false ];
			$restaurants[ 'Other' ] = [ 'name' => 'Other', 'checked' => false ];
			echo json_encode( $restaurants );exit;
		}

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
			echo json_encode( [ 'error' => 'Required fields: ' . join( ', ', $error['required'] ) . '!' ] );exit;;
			exit;
		}

		if( trim( $restaurants ) == '' ){
			echo json_encode( [ 'error' => 'Please select at least one restaurant!' ] );exit;
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
