<?php

class Controller_payments_sendfax extends Crunchbutton_Controller_Rest {
  public function init() {
    $fax_number = ($env == 'live' ? $this->request()['fax_number'] : '_PHONE_');
    $html_string = $this->request()['html'];
    $rsp = Crunchbutton_Phaxio::fax_html($fax_number, $html_string);
    c::view()->rsp = $rsp;
    c::view()->layout('layout/ajax');
    c::view()->display('payments/sendfax');
  }
}
