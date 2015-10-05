ALTER TABLE `admin_score` ADD COLUMN `experience` TINYINT(11) NOT NULL DEFAULT 1;

ALTER TABLE `order_logistics_parking` CHANGE COLUMN `parking_duration` `parking_duration1` SMALLINT(11) unsigned DEFAULT 0;
ALTER TABLE `order_logistics_parking` ADD COLUMN `parking_duration0` SMALLINT(11) unsigned DEFAULT 0 after day_of_week;
ALTER TABLE `order_logistics_parking` ADD COLUMN `parking_duration2` SMALLINT(11) unsigned DEFAULT 0 after parking_duration1;

ALTER TABLE `order_logistics_service` CHANGE COLUMN `service_duration` `service_duration1` SMALLINT(11) unsigned DEFAULT 0;
ALTER TABLE `order_logistics_service` ADD COLUMN `service_duration0` SMALLINT(11) unsigned DEFAULT 0 after day_of_week;
ALTER TABLE `order_logistics_service` ADD COLUMN `service_duration2` SMALLINT(11) unsigned DEFAULT 0 after service_duration1;


