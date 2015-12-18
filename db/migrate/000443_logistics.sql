ALTER TABLE `order_priority` ADD COLUMN `num_unpickedup_preorders` TINYINT(11) NOT NULL DEFAULT -1 after is_probably_inactive;
ALTER TABLE `order_priority` ADD COLUMN `num_unpickedup_pos_in_range` TINYINT(11) NOT NULL DEFAULT -1 after num_unpickedup_preorders;
ALTER TABLE `order_priority` ADD COLUMN `num_orders_bundle_check` TINYINT(11) NOT NULL DEFAULT -1 after num_unpickedup_pos_in_range;
