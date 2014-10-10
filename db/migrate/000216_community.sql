ALTER TABLE `community` ADD `close_all_restaurants` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `community` ADD `close_all_restaurants_note` VARCHAR( 250 ) NULL DEFAULT NULL;
ALTER TABLE `community` ADD `close_3rd_party_delivery_restaurants` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `community` ADD `close_3rd_party_delivery_restaurants_note` VARCHAR( 250 ) NULL DEFAULT NULL;