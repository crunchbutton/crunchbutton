ALTER TABLE `order_priority` ADD COLUMN `num_undelivered_orders` TINYINT(11) NOT NULL DEFAULT -1 after priority_expiration;
ALTER TABLE `order_priority` ADD COLUMN `num_drivers_with_priority` TINYINT(11) NOT NULL DEFAULT -1 after num_undelivered_orders;
ALTER TABLE `order_priority` ADD COLUMN `is_probably_inactive` TINYINT(11) NOT NULL DEFAULT 0 after num_drivers_with_priority;
