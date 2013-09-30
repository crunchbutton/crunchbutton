<?php

class Controller_payments_lastpayment extends Crunchbutton_Controller_Account {
  public function init() {

		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}
  	
    c::view()->layout('layout/ajax');
    c::view()->display('payments/lastpayment');
  }
}
