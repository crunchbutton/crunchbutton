CREATE TABLE `community_shift` (
  `id_community_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL, 
	`day` enum('mon','tue','wed','thu','fri','sat','sun') DEFAULT NULL,
  `start` varchar(5) DEFAULT '',
  `end` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id_community_shift`),
  KEY `community_shift_ibfk_1` (`id_community`),
  CONSTRAINT `community_shift_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;