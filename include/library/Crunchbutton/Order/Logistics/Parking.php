<?php

class Crunchbutton_Order_Logistics_Parking extends Cana_Table
{
    const DEFAULT_TIME = 5; // minutes

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_parking')
            ->idVar('id_order_logistics_parking')
            ->load($id);
    }

}