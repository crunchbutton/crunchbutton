<?php

namespace Balanced;

use Balanced\Core\Resource;
use Balanced\Core\URISpec;

/**
 * Represents an account bank account.
 * 
 * You can create these via Balanced\Marketplace::bank_accounts::create or
 * Balanced\Marketplace::createBankAccount. Associate them with a buyer or
 * merchant one creation via Balanced\Marketplace::createBuyer or
 * Balanced\Marketplace::createMerchant and with an existing buyer or merchant
 * use Balanced\Account::addBankAccount.
 * 
 * <code>
 * $marketplace = \Balanced\Marketplace::mine();
 * 
 * $bank_account = $marketplace->bank_accounts->create(array(
 *     'name' => 'name',
 *     'account_number' => '11223344',
 *     'bank_code' => '1313123',
 *     ));
 *     
 * $account = $marketplace
 *     ->accounts
 *     ->query()
 *     ->filter(Account::f->email_address->eq('merchant@example.com'))
 *     ->one();
 * $account->addBankAccount($bank_account->uri);
 * </code>
 */
class BankAccount extends Resource
{
    protected static $_uri_spec = null;
    
    public static function init()
    {
        self::$_uri_spec = new URISpec('bank_accounts', 'id', '/v1');
        self::$_registry->add(get_called_class());
    }
    
    public function credit(
            $amount,
            $description = null,
            $meta = null,
            $appears_on_statement_as = null)
    {
        if ($this->account == null) {
            throw new \UnexpectedValueException('Bank account is not associated with an account.');
        }
        return $this->account->credit(
            $amount,
            $description,
            $meta,
            $this->uri,
            $appears_on_statement_as);
    }
}
