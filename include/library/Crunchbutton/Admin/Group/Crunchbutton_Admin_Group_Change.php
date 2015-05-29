<?php

class Crunchbutton_Admin_Group_Change extends Cana_Table {

    public function __construct($id = null) {
        parent::__construct();
        $this
            ->table('admin_group_change')
            ->idVar('id_admin_group_change')
            ->load($id);
    }

}