/* Table with the Driver's documents */
CREATE TABLE `driver_document` (
  `id_driver_document` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `order` int(4) unsigned DEFAULT NULL,
  `url` text DEFAULT NULL,
  PRIMARY KEY (`id_driver_document`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


INSERT INTO `driver_document` (`id_driver_document`, `name`, `url`, `order`)
VALUES
	(1, 'Independant Contractor Agreement', '_URL_', 1),
	(2, 'W9', '_URL_', 2),
	(3, 'Direct Deposit', '_URL_', 3),
	(4, 'Drivers License', '', 4),
	(5, 'Insurance Card','', 5);

/* Table with the path to driver's docs */
CREATE TABLE `driver_document_status` (
  `id_driver_document_status` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_document` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `datetime` DATETIME NULL,
  `file` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_driver_document_status`),
  KEY `driver_document_status_ibfk_1` (`id_driver_document`),
  KEY `driver_document_status_ibfk_2` (`id_admin`),
  CONSTRAINT `driver_document_status_ibfk_1` FOREIGN KEY (`id_driver_document`) REFERENCES `driver_document` (`id_driver_document`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `driver_document_status_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `driver_log` (
  `id_driver_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `action` enum('created','notified','document') DEFAULT NULL,
  `info` varchar(200) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id_driver_log`),
  KEY `driver_log_ibfk_1` (`id_admin`),
  CONSTRAINT `driver_log_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;