ALTER TABLE `promo_group` ADD `range` VARCHAR( 255 ) NULL DEFAULT NULL;
ALTER TABLE `promo_group` ADD `date_mkt` DATE NULL DEFAULT NULL;
ALTER TABLE `promo_group` ADD `community` VARCHAR( 255 ) NULL DEFAULT NULL;
ALTER TABLE `promo_group` ADD `promotion_type` VARCHAR( 255 ) NULL DEFAULT NULL;
ALTER TABLE `promo_group` ADD `description` text NULL DEFAULT NULL;
ALTER TABLE `promo_group` ADD `man_hours` int(11) NULL DEFAULT NULL;
