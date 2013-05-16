<?php

class Crunchbutton_Support_Note extends Cana_Table {

  public function __construct($id = null) {
    parent::__construct();
    $this
      ->table('support_note')
      ->idVar('id_support_note')
      ->load($id);
  }

  public function notify() {
    // TODO
  }

  public function save() {
    date_default_timezone_set('UTC'); // always save in utc
    $this->datetime = date('Y-m-d H:i:s e');
    parent::save();
  }

}
