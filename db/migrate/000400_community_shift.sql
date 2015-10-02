ALTER TABLE `community_shift` ADD `id_admin` int(11) unsigned DEFAULT NULL;
ALTER TABLE `community_shift` ADD KEY `community_shift_ibfk_3` (`id_admin`);
ALTER TABLE `community_shift` ADD CONSTRAINT `community_shift_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `community_shift` ADD `date` datetime DEFAULT NULL;