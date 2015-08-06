ALTER TABLE `order_logistics_parking` MODIFY COLUMN `day_of_week` TINYINT(11) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `order_logistics_ordertime` MODIFY COLUMN `day_of_week` TINYINT(11) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `order_logistics_communityspeed` MODIFY COLUMN `day_of_week` TINYINT(11) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `order_logistics_cluster` MODIFY COLUMN `day_of_week` TINYINT(11) unsigned NOT NULL DEFAULT 0;


