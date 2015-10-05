<?php

class Cockpit_Admin_Score extends Cana_Table
{

    const DEFAULT_SCORE = 1.0;
    const DEFAULT_EXPERIENCE = 2; // 1 = newbie, 2 = regular, 3 = superstar

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('admin_score')
            ->idVar('id_admin_score')
            ->load($id);
    }
}