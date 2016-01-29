<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		echo '<pre>';var_dump( strval( 0 ) );;
		echo "\n";var_dump( strval( "0" ) );;
		echo "\n";var_dump( strval( false ) );;
		echo "\n";var_dump( strval( true ) );;

		// if( $this->request()[ 'close_all_restaurants' ] ){
			$c = Community::o( 6 );
			$c->close_all_restaurants = $this->request()[ 'close_all_restaurants' ];
			$c->save();
		// }


		echo "c->close_all_restaurants: " . $c->close_all_restaurants;

	}
}
