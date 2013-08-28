<?php

class Controller_referral extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->referral = $referral;
		c::view()->display('referral/index');	
	}
}