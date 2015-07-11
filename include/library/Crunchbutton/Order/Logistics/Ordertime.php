<?php

class Crunchbutton_Order_Logistics_Ordertime extends Cana_Table
{
    const DEFAULT_TIME = 15; // minutes

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_ordertime')
            ->idVar('id_order_logistics_ordertime')
            ->load($id);
    }
}