CREATE TABLE `log_monitor` (
  `id_log_monitor` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_log` int(11) unsigned DEFAULT NULL,
  `type` enum('credit-card-fail') DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_log_monitor`),
  KEY `log_monitor_ibfk_1` (`id_log`),
  CONSTRAINT `log_monitor_ibfk_1` FOREIGN KEY (`id_log`) REFERENCES `log` (`id_log`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;