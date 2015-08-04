CREATE TABLE `admin_shift_assign_confirmation` (
  `id_admin_shift_assign_confirmation` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_shift_assign` int(11) unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` enum('text','call','ticket') DEFAULT 'text',
  PRIMARY KEY (`id_admin_shift_assign_confirmation`),
  KEY `admin_shift_assign_confirmation_ibfk_1` (`id_admin_shift_assign`),
  CONSTRAINT `admin_shift_assign_confirmation_ibfk_1` FOREIGN KEY (`id_admin_shift_assign`) REFERENCES `admin_shift_assign` (`id_admin_shift_assign`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE  `admin_shift_assign` ADD  `confirmed` TINYINT( 1 ) NOT NULL DEFAULT '0';