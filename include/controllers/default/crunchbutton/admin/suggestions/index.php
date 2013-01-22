<?php

class Controller_Admin_Suggestions extends Crunchbutton_Controller_Account {
	public function init() {
		$suggestion = Suggestion::o(c::getPagePiece(2));
		c::view()->page = 'admin/suggestions';
		c::view()->layout('layout/admin');
		if( $suggestion->id_suggestion ){
			// Show the suggestion's form
			c::view()->suggestion = $suggestion;
			c::view()->display('admin/suggestions/suggestion');	
		} else {
			// Show the suggestions's list
			c::view()->display('admin/suggestions/index');
		}
	}
}