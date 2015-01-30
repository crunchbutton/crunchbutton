<?php

class Controller_api_test extends Cana_Controller {

	public function init(){


		$pex = new Crunchbutton_Pexcard_Resource;
		echo '<pre>';var_dump( $pex->ping() );exit();

		// $buss = new Crunchbutton_Pexcard_Business;
		// echo '<pre>';var_dump( $buss->profile() );exit();

		// $start = '01/01/2015';
		// $end = '01/25/2015';
		// $tranactios = Crunchbutton_Pexcard_Transaction::transactions( $start, $end );
		// echo '<pre>';var_dump( $tranactios );exit();

	}

}