ALTER TABLE `order` ADD COLUMN `preordered` tinyint(1) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `order` ADD COLUMN `preorder_processed` tinyint(1) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `order` ADD COLUMN `date_delivery` datetime DEFAULT NULL;
ALTER TABLE `order` ADD COLUMN `preordered_date` datetime DEFAULT NULL;

ALTER TABLE `restaurant` ADD COLUMN `allow_preorder` tinyint(1) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `community` ADD COLUMN `allow_preorder` tinyint(1) unsigned NOT NULL DEFAULT 0;
