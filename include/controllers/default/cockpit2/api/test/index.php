<?php

class Controller_api_test extends Cana_Controller {
	public function init(){
		$reward = new Crunchbutton_Reward;
		$points = $reward->processOrder( 68885 );
		echo '<pre>';var_dump( $points );exit();
	}
}