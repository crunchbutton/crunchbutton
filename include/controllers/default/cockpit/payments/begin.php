<?php

class Controller_payments_begin extends Crunchbutton_Controller_Account {
  public function init() {

    if($_REQUEST['order_by'] == 'last_payment') {
      $q = 'select '
          .'  restaurant.*, max(p.date) as "last_pay", p.`id_restaurant` as "p_id_rest" '
          .'  from restaurant '
          .'  left outer join (select id_restaurant,`date` from `payment`) as p using(id_restaurant) '
          .'  where active=1 '
          .((!$_REQUEST['payment_method'])?'':' and `payment_method`="'.$_REQUEST['payment_method'].'" ')
          .'  group by id_restaurant '
          .'  order by '
          .'    (case when p_id_rest is null then 1 else 0 end) asc,'
          .'    last_pay asc ';
    }
    else {
      $q = 'select * from restaurant where active=1 ';
      if($_REQUEST['payment_method']) {
        $q.=" and `payment_method`='".$_REQUEST['payment_method']."' ";
      }
      $q.= ' order by name asc ';
    }
/*    die($q); */


    $restaurants = Restaurant::q($q);
    $dates = explode(',',$_REQUEST['dates']);
    $search = [];
    $search['start'] = $dates[0];
    $search['end'] = $dates[1];

    $included_restaurants = [];
    $included_orders = [];
    foreach($restaurants as $restaurant) {
      array_push($included_restaurants, $restaurant);
      $orders = $restaurant->getPayableOrders($search);
      $included_orders['#RST'.$restaurant->id] = [];
      foreach($orders as $order) {
        // more orders logic here
        array_push($included_orders['#RST'.$restaurant->id], $order);
      }
    }

    c::view()->restaurants = $included_restaurants;
    c::view()->orders = $included_orders;
    c::view()->layout('layout/ajax');
    c::view()->display('payments/begin');
  }
}
