ALTER TABLE `group` ADD `description` VARCHAR(255) NULL;

ALTER TABLE `group` MODIFY `name` VARCHAR(20);

INSERT INTO `config` (`key`, `value`) VALUES ( 'notification-max-call-support-group-name', 'max-call-support' );
INSERT INTO `config` (`key`, `value`) VALUES ( 'notification-max-call-recall-after-min', '3' );
INSERT INTO `config` (`key`, `value`) VALUES ( 'notification-max-call-support-say', "press 1 to confirm you've received this call. otherwise, we will call you back fucker ." );

INSERT INTO `group` (`name`, `description`) VALUES ( 'max-call-support', 'Users will receive the max call' );