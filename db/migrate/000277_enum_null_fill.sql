-- migrations to fix a number of "Lazy MySQL" issues so we can export our data into postgres
UPDATE restaurant_payment_type
SET payment_method = NULL
WHERE payment_method = '';


-- remove NOT NULL restriction
ALTER TABLE `order` MODIFY `processor` enum('stripe','balanced') DEFAULT 'stripe';


UPDATE `order`
SET processor = NULL
WHERE processor = '';


-- remove NOT NULL restriction
ALTER TABLE admin_payment_type MODIFY `payment_type` enum('orders','hours') DEFAULT 'orders';


UPDATE admin_payment_type
SET payment_type = NULL
WHERE payment_type = '';


-- remove NOT NULL restriction
ALTER TABLE admin_payment_type MODIFY `payment_method` enum('deposit') DEFAULT 'deposit';


UPDATE admin_payment_type
SET payment_method = NULL
WHERE payment_method = '';


-- remove NOT NULL restriction
ALTER TABLE `option` MODIFY `type` enum('check','select') DEFAULT 'check';


UPDATE `option`
SET `type` = NULL
WHERE `type` = '';


-- remove NOT NULL restriction
ALTER TABLE restaurant MODIFY `delivery_min_amt` enum('total','subtotal') DEFAULT 'subtotal';


UPDATE restaurant
SET delivery_min_amt = NULL
WHERE delivery_min_amt = '';


-- remove NOT NULL restriction
ALTER TABLE restaurant MODIFY `confirmation_type` enum('regular','stealth') DEFAULT 'regular';


UPDATE restaurant
SET confirmation_type = NULL
WHERE confirmation_type = '';


-- remove NOT NULL restriction
ALTER TABLE support MODIFY `status` enum('open','closed') DEFAULT 'open';


UPDATE support
SET status = NULL
WHERE status = '';


UPDATE user_payment_type
SET card_type = NULL
WHERE card_type = '';

 -- no alter table needed

UPDATE driver_log
SET action = NULL
WHERE action = '';

 -- no alter table needed

UPDATE notification_log
SET `type` = NULL
WHERE `type` = '';


UPDATE order_action
SET `type` = NULL
WHERE `type` = '';


-- fix bad date_start (not allowed by postgres)
UPDATE restaurant_hour_override
SET `date_start` = NULL
WHERE `date_start` = "0000-00-00 00:00:00";

-- fix bad date_start and date_end
UPDATE community_shift
SET `date_start` = NULL
WHERE `date_start` = "0000-00-00 00:00:00";

UPDATE community_shift
SET `date_end` = NULL
WHERE `date_end` = "0000-00-00 00:00:00";
