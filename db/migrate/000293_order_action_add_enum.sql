ALTER TABLE `order_action` CHANGE `type` `type`
ENUM('delivery-pickedup','delivery-accepted','delivery-rejected','delivery-delivered','delivery-transfered','restaurant-accepted','restaurant-rejected','restaurant-ready', 'delivery-text-5min')
CHARACTER SET utf8
COLLATE utf8_general_ci
NULL  DEFAULT NULL;
