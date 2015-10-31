ALTER TABLE `support` CHANGE `type` `type` ENUM('SMS','BOX_NEED_HELP','WARNING','TICKET','COCKPIT_CHAT','EMAIL');
ALTER TABLE `support` ADD `email` VARCHAR(100);

ALTER TABLE `support_message` CHANGE `type` `type` ENUM('sms','note','auto-reply','warning','email');
ALTER TABLE `support_message` ADD `subject` VARCHAR(100);