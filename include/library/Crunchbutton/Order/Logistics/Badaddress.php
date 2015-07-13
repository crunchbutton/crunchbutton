<?php

class Crunchbutton_Order_Logistics_Badaddress extends Cana_Table
{
    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_badaddress')
            ->idVar('id_order_logistics_badaddress')
            ->load($id);
    }
}