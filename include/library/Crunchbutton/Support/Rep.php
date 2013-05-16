<?php

class Crunchbutton_Support_Rep extends Cana_Table {

  public function __construct($id = null) {
    parent::__construct();
    $this
      ->table('support_rep')
      ->idVar('id_support_rep')
      ->load($id);
  }
}
