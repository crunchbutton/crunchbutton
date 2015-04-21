CREATE TABLE `queue` (
  `id_queue` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `type` enum('order','notification-driver','order-confirm','order-receipt') DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `status` enum('new','running','success','failed','stopped') DEFAULT NULL,
  PRIMARY KEY (`id_queue`),
  KEY `id_order` (`id_order`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;