<?php

class Crunchbutton_Order_Logistics_Bundleparam extends Cana_Table
{
    const CUTOFF_AT_ZERO = 5;
    const SLOPE_PER_MINUTE = 0.5;
    const MAX_MINUTES = 10;
    const BASELINE_MPH = 10;

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_bundleparam')
            ->idVar('id_order_logistics_bundleparam')
            ->load($id);
    }

}