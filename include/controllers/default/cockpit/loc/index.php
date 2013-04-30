<?php

class Controller_loc extends Crunchbutton_Controller_Account {
	public function init() {
		if( c::getPagePiece(2) == 'export' ){
			c::view()->layout('layout/blank');
			c::view()->places = Crunchbutton_Loc_Log::all();
			c::view()->display('loc/table');
		} else {
			c::view()->page = 'loc';

			c::view()->total = Crunchbutton_Loc_Log::countAll();
			c::view()->cities = Crunchbutton_Loc_Log::countCities();
			c::view()->last = Crunchbutton_Loc_Log::last();
			c::view()->display('loc/index');
		}
	}
}