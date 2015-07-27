INSERT INTO `cron_log` ( `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
  ( 'Executes the restaurants scheduled payments', 'Crunchbutton_Cron_Job_PayRestaurants', '2015-07-27 10:00:00', 'minute', 1, 'idle', null, null, 0);
