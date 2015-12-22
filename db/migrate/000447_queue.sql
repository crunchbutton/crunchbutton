ALTER TABLE `queue` ADD `id_cron_log` int(11) unsigned DEFAULT NULL;
ALTER TABLE `queue` ADD KEY `queue_ibfk_5` (`id_cron_log`);
ALTER TABLE `queue` ADD CONSTRAINT `queue_ibfk_5` FOREIGN KEY (`id_cron_log`) REFERENCES `cron_log` (`id_cron_log`) ON DELETE SET NULL ON UPDATE SET NULL;