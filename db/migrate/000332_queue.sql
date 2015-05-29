ALTER TABLE queue CHANGE COLUMN `type` `type`
enum(	'order',
			'order-confirm',
			'order-receipt',
			'order-pexcard-funds',
			'notification-driver',
			'notification-your-driver',
			'notification-minutes-way') DEFAULT NULL;