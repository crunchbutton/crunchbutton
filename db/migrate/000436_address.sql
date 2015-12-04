CREATE TABLE `address` (
  `id_address` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `address` TEXT DEFAULT NULL,
  `status` enum('approved','blocked') DEFAULT NULL,
  PRIMARY KEY (`id_address`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `order` ADD COLUMN `id_address` int(11) unsigned DEFAULT NULL;
ALTER TABLE `order` ADD KEY `id_address` (`id_address`);
ALTER TABLE `order` ADD CONSTRAINT `order_ibfk_11` FOREIGN KEY (`id_address`) REFERENCES `address` (`id_address`) ON DELETE CASCADE ON UPDATE CASCADE;