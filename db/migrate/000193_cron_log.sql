INSERT INTO `cron_log` ( `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`)
VALUES
  ('Schedule warning - Sent on Sun at 1 PM PDT', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-06-01 13:00:00', 'week', 1, 'idle', '2014-06-01 13:00:00', NULL, 0),
  ('Schedule warning - Sent on Sun at 6 PM PDT', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-06-01 18:00:00', 'week', 1, 'idle', '2014-06-01 18:00:00', NULL, 0),
  ('Schedule warning - Sent on Mon at 10 AM PDT', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-06-02 10:00:00', 'week', 1, 'idle', '2014-06-02 10:00:00', NULL, 0),
  ('Schedule warning - Sent on Mon at 4 PM PDT', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-06-02 16:00:00', 'week', 1, 'idle', '2014-06-02 16:00:00', NULL, 0),
  ('Schedule warning - Sent on Mon at 4:55 PM PDT', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-06-02 16:55:00', 'week', 1, 'idle', '2014-06-02 16:55:00', NULL, 0),
  ('Schedule warning - Sent on Mon at 5 PM PDT', 'Crunchbutton_Cron_Job_DriversScheduleWarning', '2014-06-02 17:00:00', 'week', 1, 'idle', '2014-06-02 17:00:00', NULL, 0);

DELETE FROM `cron_log` WHERE id_cron_log = 1;