INSERT INTO `config` (`id_config`, `id_site`, `key`, `value`, `exposed`) VALUES (NULL, NULL, 'processor_settlement', 'balanced', '0');

ALTER TABLE payment_schedule ADD COLUMN `processor` enum('stripe','balanced') DEFAULT 'balanced';

UPDATE payment_schedule SET processor = 'balanced';