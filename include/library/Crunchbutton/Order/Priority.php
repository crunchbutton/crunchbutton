<?php

/**
 * Class Crunchbutton_Order_Priority
 */
class Crunchbutton_Order_Priority extends Cana_Table
{
    const PRIORITY_NO_ONE = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_LOW = 3;
    const PRIORITY_SKIP_NO_GEO = 4;

    const COMPLEX_CUTOFF = 10000;

    /**
     * @param $seconds
     * @param $id_admin
     * @param $id_restaurant
     * @return Cana_Iterator
     */
    public static function priorityOrders($seconds, $id_admin, $id_restaurant)
    {
        // TODO: Refactor some more
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $interval = $now->format('Y-m-d H:i:s');
        if (is_null($id_restaurant)) {
            $query = 'SELECT p.* FROM order_priority p WHERE p.id_admin= ? AND p.priority_time >= ?';
            return Crunchbutton_Order_Priority::q($query, [$id_admin, $interval]);
        }
        else {
            $query = 'SELECT p.* FROM order_priority p WHERE p.id_admin= ? and p.id_restaurant = ? AND p.priority_time >= ?';
            return Crunchbutton_Order_Priority::q($query, [$id_admin, $id_restaurant, $interval]);
        }
    }


    public static function lastNExpiredSpecialPriorityOrders($minDtString, $id_admin, $limit)
    {
        // Special means no undelivered orders when priority was given and no ties and order was not accepted by priority driver
        //  Does not take into account whether driver accepted within the window (due to possible delays)
        $query = 'SELECT p.*, oa.id_order_action FROM order_priority p left outer join order_action as oa using (id_order, id_admin) WHERE
            oa.type is null and p.priority_algo_version= ? and
            p.priority_given= ? and p.num_undelivered_orders = 0 and p.num_drivers_with_priority=1 and
            p.id_admin= ? AND p.priority_time >= ? and p.priority_expiration <= now() order by p.priority_expiration desc limit '.intval($limit);

        return Crunchbutton_Order_Priority::q($query, [Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX_ALGO_VERSION, Crunchbutton_Order_Priority::PRIORITY_HIGH, $id_admin, $minDtString]);
    }

    public static function getNumDriverOrderActionsSince($minDtString, $id_admin) {
        $query = 'SELECT COUNT(*) AS total FROM `order_action` WHERE type in ("delivery-accepted", "delivery-pickedup", "delivery-delivered")
          and timestamp >= ? and id_admin = ?';
        $row = Cana::db()->get( $query, [$minDtString, $id_admin])->get(0);
        if (intval( $row->total)){
            return intval($row->total);
        }
        return 0;
    }

    /**
     * Check that an order is in the priority array and that its priority is high
     *
     * @param $id_order
     * @param $checkPriorityArray
     * @return bool
     */
    public static function checkOrderInArray($id_order, $checkPriorityArray)
    {
        if (!is_null($checkPriorityArray)) {
            foreach ($checkPriorityArray as $priority) {
                // Note that we do not use the expiration date here, even though we could.
                //  You should filter out older order_priority's $checkPriorityArray first before passing
                //  it into this function
                if ($priority->id_order == $id_order && $priority->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getDeliveryLogisticsByOrder($id_order) {
        $query = 'SELECT p.* FROM order_priority p where id_order = ?';
        $ops = Crunchbutton_Order_Priority::q($query, [$id_order]);
        if ($ops->count() == 0) {
            return 0;
        } else{
            $op = $ops->get(0);
            if ($op->priority_algo_version >= self::COMPLEX_CUTOFF ) {
                return Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX;
            } else{
                return Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE;
            }
        }
    }

    public static function getOrderedOrderPriorities($id_order) {
        $query = 'SELECT p.* FROM order_priority p where id_order = ? order by seconds_delay desc';
        return Crunchbutton_Order_Priority::q($query, [$id_order]);
    }


    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_priority')
            ->idVar('id_order_priority')
            ->load($id);
    }
}
