<?php

class Controller_suggestions extends Crunchbutton_Controller_Account {
	public function init() {
		$suggestion = Suggestion::o(c::getPagePiece(1));
		c::view()->page = 'suggestions';

		if( $suggestion->id_suggestion ){
			// Show the suggestion's form
			c::view()->suggestion = $suggestion;
			c::view()->display('suggestions/suggestion');	
		} else {
			// Show the suggestions's list
			c::view()->display('suggestions/index');
		}
	}
}