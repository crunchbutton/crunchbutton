<?php

namespace Balanced\Core;


class ItemizationIterator implements \Iterator
{
    protected $_page,
              $_offset = 0;
    
    public function __construct($resource, $uri, $data = null)
    {
        $this->_page = new Page($resource, $uri, $data);
    }
    
    // Iterator
    
    public function current()
    {
        return $this->_page->items[$this->_offset];
    }
    
    public function key()
    {
        return $this->_page->offset + $this->_offset;
    }
    
    public function next()
    {
        $this->_offset += 1;
        if ($this->_offset >= count($this->_page->items)) {
            $this->_offset = 0;
            $this->_page = $this->_page->next();
        }
    }
    
    public function rewind()
    {
        $this->_page = $this->_page->first();
        $this->_offset = 0;
    }
    
    public function valid()
    {
        return ($this->_page != null &&
                $this->_offset < count($this->_page->items));
    }
}


class Itemization implements \IteratorAggregate, \ArrayAccess
{
    public $resource,
           $uri;
    
    protected $_page,
              $_offset = 0,
              $_size=25;
    
    public function __construct($resource, $uri, $data = null)
    {
        $this->resource = $resource;
        $this->uri = $uri;
        if ($data != null)
            $this->_page = new Page($resource, $uri, $data);
        else
            $this->_page = null;
    }
    
    protected function _getPage($offset = null)
    {
        if ($this->_page == null) {
            $this->_offset = ($offset == null) ? 0 : $offset * $this->_size;
            $uri = $this->_buildUri();
            $this->_page = new Page($this->resource, $uri);
        }
        else if ($offset != null) {
            $offset = $offset * $this->_size;
            if ($offset != $this->_offset) {
                $this->_offset = $offset;
                $uri = $this->_buildUri();
                $this->_page = new Page($this->resource, $uri);
            }
        }
        return $this->_page;
    }
    
    protected function _getItem($offset)
    {
        $page_offset = floor($offset/$this->_size);
        $page = $this->_getPage($page_offset);
        return $page->items[$offset - $page->offset];
    }
    
    public function total()
    {
        return $this->_getPage()->total;
    }
    
    protected function _buildUri($offset = null)
    {
        # TODO: hacky but works for now
        $offset = ($offset == null) ? $this->_offset : $offset;
        if (strpos($this->uri, '?') === false)
            $uri = $this->uri . '?';
        else
            $uri = $this->uri . '&';
        $uri = $uri . 'offset=' . strval($offset);
        return $uri;
    }
    
    // IteratorAggregate
    
    public function getIterator()
    {
        $uri = $this->_buildUri($offset = 0);
        $uri = $this->_buildUri($offset = 0);
        return new ItemizationIterator($this->resource, $uri);
    }

    // ArrayAccess
    
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException(get_class($this) . ' array access is read-only');
    }
    
    public function offsetExists($offset)
    {
        return (0 <= $offset && $offset < $this->total());
    }
    
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException(get_class($this) . ' array access is read-only');
    }
    
    public function offsetGet($offset)
    {
        return $this->_getItem($offset);
    }    
}
