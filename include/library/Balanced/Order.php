<?php

namespace Balanced;

use Balanced\Resource;
use Balanced\Settings;
use \RESTful\URISpec;

class Order extends Resource
{

    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('orders', 'id', '/');
        self::$_registry->add(get_called_class());
    }


    // TODO:
    public function debitFrom(
        $source,
        $amount,
        $appears_on_statement_as = null,
        $description = null,
        $meta = null)
    {
        return $source->debit(
            $amount,
            $appears_on_statement_as,
            $description,
            $meta,
            $this->href
        );
    }

    public function creditTo(
        $destination,
        $amount,
        $description = null,
        $meta = null,
        $appears_on_statement_as = null)
    {
        return $destination->credit(
            $amount,
            $description,
            $meta,
            $appears_on_statement_as,
            $this->href
        );
    }

}