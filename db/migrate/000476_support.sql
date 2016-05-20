ALTER TABLE `support` ADD COLUMN `id_community` int(11) unsigned DEFAULT NULL AFTER `id_user`;
ALTER TABLE `support` ADD KEY `id_community` (`id_community`);
ALTER TABLE `support` ADD CONSTRAINT `support_ibfk_10` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE;
