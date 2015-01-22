ALTER TABLE `admin` ADD `id_admin_author` int(11) unsigned DEFAULT NULL;

ALTER TABLE  `admin` ADD KEY `admin_ibfk_1` (`id_admin_author`);
ALTER TABLE  `admin` ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_admin_author`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;
