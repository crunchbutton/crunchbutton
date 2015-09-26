<?php

class Crunchbutton_Order_Logistics_Param extends Cana_Table
{
    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_param')
            ->idVar('id_order_logistics_param')
            ->load($id);
    }

}