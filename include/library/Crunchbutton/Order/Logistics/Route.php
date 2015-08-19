<?php

class Crunchbutton_Order_Logistics_Route extends Cana_Table
{
    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_logistics_route')
            ->idVar('id_order_logistics_route')
            ->load($id);
    }

    static public function routesByOrder($id_order)
    {
        $qString = "SELECT * FROM `order_logistics_route` WHERE id_order= ?";
        $rbo = Crunchbutton_Order_Logistics_Route::q($qString, [$id_order]);
        if (is_null($rbo) || $rbo->count() == 0) {
            return null;
        } else {
            return $rbo;
        }
    }

    static public function routesByOrderAndDriver($id_order, $id_admin)
    {
        $qString = "SELECT * FROM `order_logistics_route` WHERE id_order = ? and id_admin = ?";
        $rbod = Crunchbutton_Order_Logistics_Route::q($qString, [$id_order, $id_admin]);
        if (is_null($rbod) || $rbod->count() == 0) {
            return null;
        } else {
            return $rbod;
        }
    }

}