/* Table with the Driver's documents */
CREATE TABLE `driver_document` (
  `id_driver_document` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `order` int(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_drivers_document`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


INSERT INTO `driver_document` (`id_driver_document`, `name`, `order`)
VALUES
	(1, 'Independant Contractor Agreement', 1),
	(2, 'W9', 2),
	(3, 'Direct Deposit', 3),
	(4, 'Drivers License', 4),
	(5, 'Insurance Card', 5);

/* Table with the path to driver's docs */
CREATE TABLE `driver_document_status` (
  `id_driver_document_status` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_document` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `url` text DEFAULT NULL,
  PRIMARY KEY (`id_driver_document_status`),
  KEY `driver_document_status_ibfk_1` (`id_driver_document`),
  KEY `driver_document_status_ibfk_2` (`id_admin`),
  CONSTRAINT `driver_document_status_ibfk_1` FOREIGN KEY (`id_driver_document`) REFERENCES `driver_document` (`id_driver_document`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `driver_document_status_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;