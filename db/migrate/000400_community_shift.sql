ALTER TABLE `community_shift` ADD `id_driver` int(11) unsigned DEFAULT NULL;
ALTER TABLE `community_shift` ADD KEY `community_shift_ibfk_3` (`id_driver`);
ALTER TABLE `community_shift` ADD CONSTRAINT `community_shift_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `community_shift` ADD `date` datetime DEFAULT NULL;