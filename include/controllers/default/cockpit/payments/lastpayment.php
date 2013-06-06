<?php

class Controller_payments_lastpayment extends Crunchbutton_Controller_Account {
  public function init() {
    c::view()->layout('layout/ajax');
    c::view()->display('payments/lastpayment');
  }
}
