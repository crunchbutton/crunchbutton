<?php

class Controller_support extends Crunchbutton_Controller_Account {
	public function init() {

		$id_support = c::getPagePiece(1);
		header( 'Location: /support/plus/' . $id_support );
		
		exit();
	}
}
