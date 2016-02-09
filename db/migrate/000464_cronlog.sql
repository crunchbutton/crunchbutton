INSERT INTO `cron_log` ( `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
	('Create a new pexcard token every 10 days', 'Crunchbutton_Cron_Job_CreatePexCardToken', '2016-02-09 09:00:00', 'days', 10, 'idle', NULL, NULL, 0);
