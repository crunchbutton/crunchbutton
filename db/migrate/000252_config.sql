DELETE FROM `config` WHERE `key` IN ( 'pex-amount-shift-start', 'pex-card-funds-shift-enable', 'pex-card-funds-order-enable', 'pex-card-funds-order-enable-for-cash' );

INSERT INTO `config` (`id_site`, `key`, `value`) VALUES (NULL,'pex_amount_shift_start','10.00');
INSERT INTO `config` (`id_site`, `key`, `value`) VALUES (NULL,'pex_card_funds_shift_enable','0');
INSERT INTO `config` (`id_site`, `key`, `value`) VALUES (NULL,'pex_card_funds_order_enable','0');
INSERT INTO `config` (`id_site`, `key`, `value`) VALUES (NULL,'pex_card_funds_order_enable_for_cash','0');