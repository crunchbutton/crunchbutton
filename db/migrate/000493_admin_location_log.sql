
CREATE TABLE `admin_location_log` (
  `id_admin_location_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_location_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
