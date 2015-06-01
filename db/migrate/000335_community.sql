ALTER TABLE `community` ADD COLUMN `id_driver_group` int(11) unsigned DEFAULT NULL;

ALTER TABLE `community` ADD KEY `id_driver_group` (`id_driver_group`);
ALTER TABLE `community` ADD CONSTRAINT `community_ibfk_5` FOREIGN KEY (`id_driver_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE;

-- fix the group name
UPDATE `group` SET name = 'drivers-baylor-not-used' WHERE id_group = 160;