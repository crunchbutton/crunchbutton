<?php

class Crunchbutton_Order_Logistics_Communityspeed extends Cana_Table
{
    const DEFAULT_MPH = 25;

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_communityspeed')
            ->idVar('id_order_logistics_communityspeed')
            ->load($id);
    }
}