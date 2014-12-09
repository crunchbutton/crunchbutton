ALTER TABLE `community` ADD `close_all_restaurants_id_admin` int(11) unsigned DEFAULT NULL;
ALTER TABLE `community` ADD `close_3rd_party_delivery_restaurants_id_admin` int(11) unsigned DEFAULT NULL;

ALTER TABLE  `community` ADD KEY `community_ibfk_2` (`close_all_restaurants_id_admin`);
ALTER TABLE  `community` ADD CONSTRAINT `community_ibfk_2` FOREIGN KEY (`close_all_restaurants_id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE  `community` ADD KEY `community_ibfk_3` (`close_3rd_party_delivery_restaurants_id_admin`);
ALTER TABLE  `community` ADD CONSTRAINT `community_ibfk_3` FOREIGN KEY (`close_3rd_party_delivery_restaurants_id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;
