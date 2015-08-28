ALTER TABLE `restaurant_payment_type` ADD `max_pay_promotion` TINYINT(1)  NOT NULL  DEFAULT '0';

UPDATE restaurant_payment_type SET charge_credit_fee = 1, waive_fee_first_month = 0, pay_apology_credits = 1, max_apology_credit = 5, max_pay_promotion = 3;