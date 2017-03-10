CREATE TABLE `log_type` (
  `id_log_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_log_type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `log` (
  `id_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_log_type` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `data` text,
  `level` enum('critical','debug','error','warning') DEFAULT 'debug',
  PRIMARY KEY (`id_log`),
  KEY `log_ibfk_1` (`id_log_type`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_log_type`) REFERENCES `log_type` (`id_log_type`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;