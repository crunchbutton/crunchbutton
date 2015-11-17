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
  `env` enum('live','crondb','local') DEFAULT 'live',
  PRIMARY KEY (`id_cron_log`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `cron_log` (`id_cron_log`, `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
  (1, 'Send schedule warning to drivers', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-05-26 09:00:00', 'day', 1, 'idle', '2014-05-29 09:00:00', '2014-05-28 09:30:10', 1),
  (2, 'Remind drivers a day before their shift', 'Crunchbutton_Cron_Job_DriversRemindAboutTheirShiftTomorrow', '2014-05-26 09:00:00', 'day', 1, 'idle', '2014-05-29 09:00:00', '2014-05-29 09:00:00', 3),
  (3, 'Renotify drivers about not confirmed orders', 'Crunchbutton_Cron_Job_RenotifyDrivers', '2014-05-26 09:00:00', 'minute', 3, 'idle', '2014-05-28 14:21:00', '2014-05-28 14:18:02', 97),
  (4, 'Job to check if the cron is running every minute', 'Crunchbutton_Cron_Job_Test', '2014-05-26 06:00:00', 'minute', 1, 'idle', '2014-05-28 14:20:00', '2014-05-28 14:19:01', 1324),
  (5, 'Remind drivers minutes before their shift', 'Crunchbutton_Cron_Job_DriversRemindMinutesAboutTheirShift', '2014-05-26 06:00:00', 'minute', 5, 'idle', '2014-05-28 14:20:00', '2014-05-28 14:15:01', 47),
  (6, 'Send Judd Emails for New Users 2pm', 'Crunchbutton_Cron_Job_NotifyAboutNewUsers', '2014-05-29 14:00:00', 'day', 1, 'idle', '2014-05-29 14:00:00', NULL, 0),
  (7, 'Send Judd Emails for New Users 5pm', 'Crunchbutton_Cron_Job_NotifyAboutNewUsers', '2014-05-28 17:00:00', 'day', 1, 'idle', '2014-05-28 17:00:00', NULL, 0),
  (8, 'Send Judd Emails for New Users 8pm', 'Crunchbutton_Cron_Job_NotifyAboutNewUsers', '2014-05-28 20:00:00', 'day', 1, 'idle', '2014-05-28 20:00:00', NULL, 0),
  (9, 'Send Judd Emails for New Users 11pm', 'Crunchbutton_Cron_Job_NotifyAboutNewUsers', '2014-05-28 23:00:00', 'day', 1, 'idle', '2014-05-28 23:00:00', NULL, 0);
