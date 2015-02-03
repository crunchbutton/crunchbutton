ALTER TABLE `community` ADD  `id_driver_restaurant` int(11) unsigned DEFAULT NULL;
ALTER TABLE `community` ADD KEY `community_ibfk_4` (`id_driver_restaurant`);
ALTER TABLE `community` ADD CONSTRAINT `community_ibfk_4` FOREIGN KEY (`id_driver_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL;