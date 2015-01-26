ALTER TABLE `group` ADD `type` enum('driver','marketing-rep' ) DEFAULT NULL;

ALTER TABLE `group` ADD  `id_community` int(11) unsigned DEFAULT NULL;
ALTER TABLE `group` ADD KEY `group_ibfk_1` (`id_community`);
ALTER TABLE `group` ADD CONSTRAINT `group_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL;
