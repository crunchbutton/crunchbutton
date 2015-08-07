<?php

class Controller_api_sandbox extends Crunchbutton_Controller_Rest {

	public function init() {

		$address = "311 Highland Lake Circle Decatur, GA, 30033";
    $location = Crunchbutton_GoogleGeocode::geocode($address);
    echo '<pre>';var_dump( $location );exit();

	}
}
