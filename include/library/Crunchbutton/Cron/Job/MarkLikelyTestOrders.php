<?php
class Crunchbutton_Cron_Job_MarkLikelyTestOrders extends Crunchbutton_Cron_Log {
    // description: mark likely test orders in database
    // class: Crunchbutton_Cron_Job_MarkLikelyTestOrders
    // interval: minute
    // interval_unit: 10
    public function run() {
        // updates orders that meet our likely test criteria and have not been changed recently.
        $queryResult = c::dbWrite()->query("UPDATE `order` O
            JOIN community ON community.id_community = O.id_community
            JOIN restaurant ON restaurant.id_restaurant = O.id_restaurant
            SET likely_test = (community.name LIKE '%test%' OR restaurant.name LIKE '%test%' OR O.name LIKE '%test%' OR (O.address LIKE '%test%' AND O.address IS NOT NULL))
            WHERE likely_test IS NULL");
        $this->finished();
    }
}
?>
