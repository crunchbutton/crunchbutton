ALTER TABLE support_message CHANGE COLUMN `type` `type` enum('sms','note','auto-reply','warning') NOT NULL DEFAULT 'sms';