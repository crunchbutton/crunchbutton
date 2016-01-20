ALTER TABLE `queue` ADD `id_pexcard_action` int(11) unsigned DEFAULT NULL;
ALTER TABLE `queue` ADD KEY `queue_ibfk_6` (`id_pexcard_action`);
ALTER TABLE `queue` ADD CONSTRAINT `queue_ibfk_6` FOREIGN KEY (`id_pexcard_action`) REFERENCES `pexcard_action` (`id_pexcard_action`) ON DELETE SET NULL ON UPDATE SET NULL;