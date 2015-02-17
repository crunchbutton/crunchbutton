<?php
class Crunchbutton_Cron_Job_MarkLikelyTestOrders extends Crunchbutton_Cron_Log {
    // description: mark likely test orders in database
    // class: Crunchbutton_Cron_Job_MarkLikelyTestOrders
    // interval: minute
    // interval_unit: 20
    public function run() {
        $queryResult = c::db()->query("UPDATE `order` O
            JOIN community ON community.id_community = O.id_community
            JOIN restaurant ON restaurant.id_restaurant = O.id_restaurant
            SET likely_test = (community.name LIKE '%test%' OR restaurant.name LIKE '%test%' OR O.name LIKE '%test%' OR (O.address LIKE '%test%' AND O.address IS NOT NULL))
            WHERE likely_test IS NULL");
        // force query to run out and apply everywhere
        while($r = $queryResult->fetch()) {
        }
        $this->finished();
    }
}
?>
