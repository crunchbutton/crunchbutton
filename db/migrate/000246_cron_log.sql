INSERT INTO `cron_log` ( `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
	('Check if the card has more than $500', 'Crunchbutton_Cron_Job_CheckBalanceLimit', '2014-12-03 05:00:00', 'hour', 2, 'idle', NULL, NULL, 0);
