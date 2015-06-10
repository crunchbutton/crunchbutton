<?php

class Controller_referral extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global','invite-promo'])) {
			return ;
		}

		header('HTTP/1.1 301 Moved Permanently');
		header('Location: https://cockpit.la/config/rewards/');


		c::view()->referral = $referral;
		c::view()->display('referral/index');
	}
}