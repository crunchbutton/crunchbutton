CREATE TABLE `cron_log` (
  `id_cron_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(40) DEFAULT NULL,
  `command` varchar(200) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `interval` enum('minute','hour','day','week') DEFAULT 'day',
  `interval_unity` tinyint(2) NOT NULL DEFAULT '1',
  `current_status` enum('idle','running') DEFAULT 'idle',
  `next_time` datetime DEFAULT NULL,
  `last_time` datetime DEFAULT NULL,
  `last_time_status` enum('error','success') DEFAULT NULL,
  `interactions` int(11) DEFAULT '0',
  PRIMARY KEY (`id_cron_log`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;