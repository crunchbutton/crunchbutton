<?php

class Crunchbutton_Order_Logistics_Fakecustomer extends Cana_Table
{
    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_fakecustomer')
            ->idVar('id_order_logistics_fakecustomer')
            ->load($id);
    }
}