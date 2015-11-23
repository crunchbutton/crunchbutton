ALTER TABLE `order` ADD `delivery_status` int(11) unsigned DEFAULT NULL;
ALTER TABLE `order` ADD KEY `order_ibfk_10` (`delivery_status`);
ALTER TABLE `order` ADD CONSTRAINT `order_ibfk_10` FOREIGN KEY (`delivery_status`) REFERENCES `order_action` (`id_order_action`) ON DELETE SET NULL ON UPDATE SET NULL;