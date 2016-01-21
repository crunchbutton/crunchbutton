CREATE TABLE `log_type` (
  `id_log_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_log_type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `log` ADD `id_log_type` int(11) unsigned DEFAULT NULL;
ALTER TABLE `log` ADD KEY `log_ibfk_1` (`id_log_type`);
ALTER TABLE `log` ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_log_type`) REFERENCES `log_type` (`id_log_type`) ON DELETE SET NULL ON UPDATE SET NULL;

INSERT INTO `log_type` (`type`)
VALUES
		('account-js'),
		('admin-hours'),
		('admin-notification'),
		('card-error'),
		('claim-account'),
		('closed'),
		('connect-call'),
		('cron-jobs'),
		('delivery-driver'),
		('dishes'),
		('dispatch-notification'),
		('driver-customer'),
		('driver-remind'),
		('driver-schedule'),
		('driver-sms'),
		('driver-warning'),
		('drivers-onboarding'),
		('game-score'),
		('gift-card-warning'),
		('incoming-sms'),
		('location-js'),
		('max-call'),
		('notification'),
		('options-dishes'),
		('options-dishes-removed'),
		('order'),
		('order-js'),
		('order-log'),
		('order-rules'),
		('pexcard'),
		('promo-email'),
		('promo-sms'),
		('referral'),
		('settlement'),
		('sms'),
		('suggestion'),
		('support'),
		('support-sms'),
		('user-sms'),
		('unknown'),
		('wrong-delivery-type');


UPDATE log SET type = 'admin-notification' WHERE type = 'admin_notification';
UPDATE log SET type = 'card-error' WHERE type = 'card error';
UPDATE log SET type = 'dispatch-notification' WHERE type = 'dispatch_notification';
UPDATE log SET type = 'gift-card-warning' WHERE type = 'gift_card_warning';
UPDATE log SET type = 'promo-email' WHERE type = 'promo_email';
UPDATE log SET type = 'promo-sms' WHERE type = 'promo_sms';
UPDATE log SET type = 'wrong-delivery-type' WHERE type = 'wrong delivery type';

UPDATE log l INNER JOIN log_type lt ON l.`type` = lt.`type` SET l.id_log_type = lt.id_log_type;

UPDATE log SET id_log_type = 40 WHERE type IS NULL;