CREATE TABLE `community_shift_recursivity` (
  `id_community_shift_recursivity` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `action` enum('ignore') DEFAULT NULL,
  PRIMARY KEY (`id_community_shift_recursivity`),
  KEY `community_shift_recursivity_ibfk_1` (`id_community_shift`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;