<?php

class Controller_referral extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}
		
		c::view()->referral = $referral;
		c::view()->display('referral/index');	
	}
}