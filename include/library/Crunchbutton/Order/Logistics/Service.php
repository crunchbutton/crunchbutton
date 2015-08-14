<?php

class Crunchbutton_Order_Logistics_Service extends Cana_Table
{
    const DEFAULT_TIME = 0; // minutes

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_service')
            ->idVar('id_order_logistics_service')
            ->load($id);
    }

}