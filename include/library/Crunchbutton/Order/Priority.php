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
            $where = 'WHERE p.id_admin=? ';
            $where .= ' AND p.priority_time >= "' . $interval . '" ';
            $query = 'SELECT p.* FROM order_priority p ' . $where;
            return Crunchbutton_Order_Priority::q($query, [$id_admin]);
        }
        else {
            $where = 'WHERE p.id_admin=? AND p.id_restaurant=?';
            $where .= ' AND p.priority_time >= "' . $interval . '" ';
            $query = 'SELECT p.* FROM order_priority p  ' . $where;
            return Crunchbutton_Order_Priority::q($query, [$id_admin, $id_restaurant]);
        }
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

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('order_priority')
            ->idVar('id_order_priority')
            ->load($id);
    }
}