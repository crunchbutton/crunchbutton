<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {


		$cards = Crunchbutton_Pexcard_Card::card_list();
		echo '<pre>';var_dump( $cards );exit();


	}
}