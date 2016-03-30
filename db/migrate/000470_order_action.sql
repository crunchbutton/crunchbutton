ALTER TABLE `order_action` CHANGE `type` `type`
ENUM('delivery-pickedup','delivery-accepted','delivery-rejected','delivery-delivered','delivery-transfered','delivery-canceled','restaurant-accepted','restaurant-rejected','restaurant-ready','delivery-text-5min','ticket-not-geomatched','force-commission-payment','ticket-campus-cash','ticket-campus-cash-reminder','ticket-reps-failed-pickup');
