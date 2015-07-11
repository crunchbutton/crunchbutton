<?php

class Crunchbutton_Order_Logistics_Cluster extends Cana_Table
{

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_cluster')
            ->idVar('id_order_logistics_cluster')
            ->load($id);
    }
}