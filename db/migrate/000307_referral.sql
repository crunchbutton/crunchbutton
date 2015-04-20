ALTER TABLE  `referral` ADD  `warned` TINYINT( 1 ) NOT NULL DEFAULT '0';
UPDATE referral SET warned = 1;