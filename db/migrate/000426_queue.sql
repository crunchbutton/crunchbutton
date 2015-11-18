ALTER TABLE queue CHANGE COLUMN `type` `type` enum('order','notification-driver','order-confirm','order-receipt','notification-your-driver','settlement-driver','settlement-restaurant','restaurant-time') DEFAULT NULL;

INSERT INTO `queue_type` (`type`) VALUES ('restaurant-time');

ALTER TABLE `queue` ADD `id_restaurant` int(11) unsigned DEFAULT NULL;
ALTER TABLE `queue` ADD KEY `queue_ibfk_4` (`id_restaurant`);
ALTER TABLE `queue` ADD CONSTRAINT `queue_ibfk_4` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL;