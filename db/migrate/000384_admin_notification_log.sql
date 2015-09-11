ALTER TABLE `admin_notification_log` ADD COLUMN `id_admin` int(11) unsigned DEFAULT NULL after id_order;
ALTER TABLE `admin_notification_log` ADD CONSTRAINT `admin_notification_log_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `admin_notification_log` ADD INDEX `id_admin_idx` (id_admin);