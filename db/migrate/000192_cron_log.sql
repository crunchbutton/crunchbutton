CREATE TABLE `cron_log` (
  `id_cron_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(50) DEFAULT NULL,
  `class` varchar(200) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `interval` enum('minute','hour','day','week') DEFAULT 'day' NOT NULL,
  `interval_unity` tinyint(2) NOT NULL DEFAULT '1',
  `current_status` enum('idle','running') DEFAULT 'idle' NOT NULL,
  `next_time` datetime DEFAULT NULL,
  `finished` datetime DEFAULT NULL,
  `interactions` int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (`id_cron_log`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `cron_log` (`id_cron_log`, `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
  (1, 'Send schedule warning to drivers', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-05-26 09:00:00', 'day', 1, 'idle', NULL, NULL, 0),
  (2, 'Send txt to drivers before their shift', 'Crunchbutton_Cron_Job_DriversBeforeTheirShift', '2014-05-26 09:00:00', 'minute', 5, 'idle', NULL, NULL, 0),
  (3, 'Renotify drivers about not confirmed orders', 'Crunchbutton_Cron_Job_RenotifyDrivers', '2014-05-26 09:00:00', 'minute', 3, 'idle', NULL, NULL, 0);
