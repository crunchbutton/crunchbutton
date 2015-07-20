ALTER TABLE queue CHANGE COLUMN `type` `type` enum('order','notification-driver','order-confirm','order-receipt','notification-your-driver','order-pexcard-funds', 'notification-driver-priority') DEFAULT NULL;

INSERT INTO queue_type ( `type` ) VALUES ( 'notification-driver-priority' );
