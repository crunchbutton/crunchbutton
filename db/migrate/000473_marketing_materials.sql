CREATE TABLE `marketing_materials_refil` (
  `id_marketing_materials_refil` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `github` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_marketing_materials_refil`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `marketing_materials_refil_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;