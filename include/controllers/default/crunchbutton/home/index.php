<?php

class Controller_home extends Cana_Controller {
	public function init() {
		$c = Community::permalink(c::getPagePiece(0));
		if ($c->id_community) {
			Cana::view()->community = $c;
		}
		Cana::view()->display('home/index');
	}
}