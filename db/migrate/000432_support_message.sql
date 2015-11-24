ALTER TABLE `support_message` ADD `id_phone_log` int(11) unsigned DEFAULT NULL;
ALTER TABLE `support_message` ADD KEY `support_message_ibfk_4` (`id_phone_log`);
ALTER TABLE `support_message` ADD CONSTRAINT `support_message_ibfk_4` FOREIGN KEY (`id_phone_log`) REFERENCES `phone_log` (`id_phone_log`) ON DELETE SET NULL ON UPDATE SET NULL;