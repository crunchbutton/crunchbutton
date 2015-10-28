ALTER TABLE queue CHANGE COLUMN `type` `type` enum('order','notification-driver','order-confirm','order-receipt','notification-your-driver','settlement-driver','settlement-restaurant') DEFAULT NULL;

INSERT INTO `queue_type` (`type`) VALUES ('settlement-driver'), ('settlement-restaurant');

ALTER TABLE queue ADD COLUMN info TEXT;