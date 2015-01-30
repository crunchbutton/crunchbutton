<?php

class Crunchbutton_Pexcard_Details extends Crunchbutton_Pexcard_Resource {

	public function account(){
		return Crunchbutton_Pexcard_Resource::request( 'detailsaccount' );
	}
}

?>
