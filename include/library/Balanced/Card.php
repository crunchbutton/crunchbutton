<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/**
 * Represents an credit card.
 *
 * <code>
 * $marketplace = \Balanced\Marketplace::mine();
 *
 * $card = $marketplace->cards->create(array(
 *     'name' => 'name',
 *     'account_number' => '11223344',
 *     'bank_code' => '1313123'
 * ));
 *
 * $account = $marketplace
 *     ->accounts
 *     ->query()
 *     ->filter(Customer::f->email_address->eq('buyer@example.com'))
 *     ->one();
 * $account->addCard($card->uri);
 * </code>
 */
class Card extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('cards', 'id', '/');
        self::$_registry->add(get_called_class());
    }

    public function debit(
        $amount,
        $appears_on_statement_as = null,
        $description = null,
        $meta = null,
        $order = null)
    {
        return $this->debits->create(array(
            'amount' => $amount,
            'appears_on_statement_as' => $appears_on_statement_as,
            'description' => $description,
            'meta' => $meta,
            'order' => $order
        ));
    }

    public function hold(
        $amount,
        $description = null,
        $meta = null)
    {
        return $this->card_holds->create(array(
            'amount' => $amount,
            'description' => $description,
            'meta' => $meta
        ));
    }

    public function associateToCustomer($customer) {
        if(is_string($customer)) {
            $this->links->customer = $customer;
        } else {
            $this->links->customer = $customer->href;
        }
        $this->save();
    }

    public function invalidate()
    {
        $this->unstore();
        return $this;
    }

}
