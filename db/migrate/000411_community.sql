ALTER TABLE `community` ADD COLUMN `campus_cash` tinyint(1) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `community` ADD COLUMN `campus_cash_name` varchar(100) DEFAULT NULL;
ALTER TABLE `community` ADD COLUMN `campus_cash_validation` varchar(255) DEFAULT NULL;
ALTER TABLE `community` ADD COLUMN `campus_cash_mask` varchar(255) DEFAULT NULL;
ALTER TABLE `community` ADD COLUMN `campus_cash_fee` float DEFAULT NULL;