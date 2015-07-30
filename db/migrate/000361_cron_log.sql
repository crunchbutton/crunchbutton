INSERT INTO `cron_log` ( `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
  ( 'Check for unverified accounts and auto-reverify them', 'Crunchbutton_Cron_Job_VerifyDriverAccount', '2015-07-30 10:00:00', 'week', 1, 'idle', null, null, 0);
