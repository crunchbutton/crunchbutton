<?php

namespace Balanced;

use Balanced\Core\Resource;
use Balanced\Core\URISpec;

/**
 * Represents an account credit transaction.
 * 
 * You create these using Balanced\Account::credit.
 * 
 * <code>
 * $marketplace = \Balanced\Marketplace::mine();
 * 
 * $account = $marketplace
 *     ->accounts
 *     ->query()
 *     ->filter(Account::f->email_address->eq('merchant@example.com'))
 *     ->one();
 * 
 * $credit = $account->credit(
 *     100,
 *     'how it '
 *     array(
 *         'my_id': '112233'
 *         )
 *     );
 * </code>
 */
class Credit extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('credits', 'id');
        self::$_registry->add(get_called_class());
    }
}

