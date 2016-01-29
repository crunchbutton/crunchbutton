ALTER TABLE `log_monitor` ADD COLUMN `id_phone` int(11) unsigned DEFAULT NULL;
ALTER TABLE `log_monitor` ADD KEY `id_phone` (`id_phone`);
ALTER TABLE `log_monitor` ADD CONSTRAINT `log_monitor_ibfk_1` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE;
