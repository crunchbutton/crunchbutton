CREATE TABLE `shift_community` (
  `id_shift_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL, 
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id_shift_community`),
  KEY `shift_community_ibfk_1` (`id_community`),
  CONSTRAINT `shift_community_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;