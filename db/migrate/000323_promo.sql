ALTER TABLE `promo` ADD COLUMN `is_discount_code` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `promo` ADD COLUMN `date_start` date DEFAULT NULL;
ALTER TABLE `promo` ADD COLUMN `date_end` date DEFAULT NULL;
ALTER TABLE `promo` ADD COLUMN `id_admin` int(11) unsigned DEFAULT NULL;
ALTER TABLE `promo` ADD COLUMN `id_community` int(11) unsigned DEFAULT NULL;
ALTER TABLE `promo` ADD COLUMN `delivery_fee` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `promo` ADD COLUMN `active` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `promo` ADD COLUMN `usable_by` enum('new-users','old-users','anyone') DEFAULT NULL;

ALTER TABLE `promo` ADD KEY `id_admin` (`id_admin`);
ALTER TABLE `promo` ADD CONSTRAINT `promo_ibfk_5` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `promo` ADD KEY `id_community` (`id_community`);
ALTER TABLE `promo` ADD CONSTRAINT `promo_ibfk_6` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE;