<?php

namespace Balanced\Core;

class Resource
{
    public static $fields,
                  $f;
     
    protected static $_client,
                     $_registry,
                     $_uri_spec;
    
    protected $_collection_uris,
              $_member_uris;
    
    public static function init()
    {
        self::$_client = new Client();
        self::$_registry = new Registry();
        self::$f = self::$fields = new Fields();
    }
    
    public static function getClient()
    {
        $class = get_called_class();
        return $class::$_client;
    }
    
    public static function getRegistry()
    {
        $class = get_called_class();
        return $class::$_registry;
    }
    
    public static function getURISpec()
    {
        $class = get_called_class();
        return $class::$_uri_spec;
    }
    
    public function __construct($fields = null)
    {
        if ($fields == null)
            $fields = array();
        $this->_objectify($fields);
    }
    
    public function __get($name)
    {
        // collection uri
        if (array_key_exists($name, $this->_collection_uris)) {
            $result = $this->_collection_uris[$name];
            $this->$name = new Collection($result['class'], $result['uri']);
            return $this->$name;
        }
        // member uri
        else if (array_key_exists($name, $this->_member_uris)) {
            $result = $this->$_collection_uris[$name]; 
            $response = self::getClient().get($result['uri']);
            $class = $result['class'];
            $this->$name = new $class($response->body);
            return $this->$name;
        }
        
        // unknown
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
    
    protected function _objectify($fields)
    {
        // initialize uris
        $this->_collection_uris = array();
        $this->_member_uris = array();

        foreach ($fields as $key => $val) {
            // nested uri
            if ((strlen($key) - 3) == strrpos($key, 'uri', 0) && $key != 'uri') {
                $result = self::$_registry->match($val);
                if ($result != null) {
                    $name = substr($key, 0, -4);
                    $class = $result['class'];
                    if ($result['collection'])
                        $this->_collection_uris[$name] = array(
                            'class' => $class,
                            'uri' => $val,
                            );
                    else
                        $this->_member_uris[$name] = array(
                            'class' => $class,
                            'uri' => $val,
                            );
                    continue;
                }
            }
            // nested
            else if (is_object($val) && property_exists($val, 'uri')) {
                $result = self::$_registry->match($val->uri);
                if ($result != null) {
                    $class = $result['class'];
                    if ($result['collection'])
                        $this->$key = new Collection($class, $val['uri'], $val); 
                    else
                        $this->$key = new $class($val);
                    continue;
                }
            }

            // default
            $this->$key = $val;
        }
    }
    
    public static function query()
    {
        $uri_spec = self::getURISpec();
        if ($uri_spec == null || $uri_spec->collection_uri == null) {
            $msg = sprintf('Cannot directly query %s resources', get_called_class());
            throw new \LogicException($msg);
        }
        return new Query(get_called_class(), $uri_spec->collection_uri);
    }
    
    public static function get($uri)
    {
        $response = self::getClient()->get($uri);
        $class = get_called_class();
        return new $class($response->body);
    }
    
    public function save()
    {
        // payload
        $payload = array();
        foreach($this as $key => $val) {
            if ($key[0] == '_' || is_object($val))
                continue;
            $payload[$key] = $val;
        }

        // update 
        if (array_key_exists('uri', $payload)) {
            $uri = $payload['uri'];
            unset($payload['uri']);
            $response = self::getClient()->put($uri, $payload);
        }
        // create
        else {
            $class = get_class($this);
            if ($class::$_uri_spec == null || $class::$_uri_spec->collection_uri == null) {
                $msg = sprintf('Cannot directly create %s resources', $class);
                throw new \LogicException($msg);
            }
            $response = self::getClient()->post($class::$_uri_spec->collection_uri, $payload);
        }
        
        // re-objectify
        foreach($this as $key => $val)
            unset($this->$key);
        $this->_objectify($response->body);
        
        return $this;
    }

    public function delete()
    {
        self::getClient()->delete($this->uri);
        return $this;
    }
}
 

class Fields
{
    public function __get($name)
    {
        return new Field($name);
    }
}

class Field
{
    public $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function __get($name)
    {
        return new Field($this->name . '.' . $name);
    }

    public function in($vals)
    {
        return new FilterExpression($this->name, 'in', $vals, '!in');
    }

    public function startswith($prefix)
    {
        if (!is_string($prefix))
            throw new \InvalidArgumentException('"startswith" prefix  must be a string');
        return new FilterExpression($this->name, 'contains', $prefix);
    }

    public function endswith($suffix)
    {
        if (!is_string($suffix))
            throw new \InvalidArgumentException('"endswith" suffix  must be a string');
        return new FilterExpression($this->name, 'contains', $suffix);
    }

    public function contains($fragment)
    {
        if (!is_string($fragment))
            throw new \InvalidArgumentException('"contains" fragment must be a string');
        return new FilterExpression($this->name, 'contains', $fragment, '!contains');
    }

    public function eq($val)
    {
        return new FilterExpression($this->name, '=', $val, '!eq');
    }

    public function lt($val)
    {
        return new FilterExpression($this->name, '<', $val, '>=');
    }

    public function lte($val)
    {
        return new FilterExpression($this->name, '<=', $val, '>');
    }

    public function gt($val)
    {
        return new FilterExpression($this->name, '>', $val, '<=');
    }

    public function gte($val)
    {
        return new FilterExpression($this->name, '>=', $val, '<');
    }

    public function asc()
    {
        return new SortExpression($this->name, true);
    }

    public function desc()
    {
        return new SortExpression($this->name, false);
    }
}

class FilterExpression
{
    public $field,
           $op,
           $val,
           $not_op;

    public function __construct($field, $op, $val, $not_op = null)
    {
        $this->field = $field;
        $this->op = $op;
        $this->val = $val;
        $this->not_op = $not_op;
    }

    public function not()
    {
        if ($not_op == null)
            throw new \LogicException(sprintf('Filter cannot be inverted'));
        $temp = $this->op;
        $this->op = $this->not_op;
        $this->not_op = $temp;
        return $this;
    }
}

class SortExpression
{
    public $name,
           $ascending;

    public function __construct($field, $ascending =  true)
    {
        $this->field = $field;
        $this->ascending= $ascending;
    }
}
