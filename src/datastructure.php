<?php

namespace igorw\edn;

/** @api */
class Symbol {
    static public $symbols = [];
    public $value;

    protected function __construct($value) {
        $this->value = $value;
    }

    function __toString() {
        return $this->value;
    }

    static function get($value) {
        if (isset(static::$symbols[$value])) {
            return static::$symbols[$value];
        }

        $symbol = new static($value);
        static::$symbols[$value] = $symbol;
        return $symbol;
    }
}

/** @api */
class Keyword extends Symbol {
    static public $symbols = [];

    function __toString() {
        return ':'.$this->value;
    }
}

class Collection implements \IteratorAggregate {
    public $data;

    function __construct($data = []) {
        if ($data instanceof \Iterator || $data instanceof \IteratorAggregate)
            $data = iterator_to_array($data);
        $this->data = $this->init($data);
    }

    function init(array $data) {
        return $data;
    }

    function seq() {
        return $this->data;
    }

    function map($fn) {
        return new static(array_map($fn, $this->seq()));
    }

    function getIterator() {
        return new \ArrayIterator($this->seq());
    }
}

class LinkedList extends Collection {}

class Vector extends Collection {}

class Map extends Collection implements \ArrayAccess {
    function init(array $data) {
        $map = [];
        $partitioned = array_chunk($data, 2);
        foreach ($partitioned as $item) {
            list($key, $val) = $item;
            $key = $this->hashCode($key);
            $map[$key] = $val;
        }
        return $map;
    }

    function seq() {
        $seq = [];
        foreach ($this->data as $key => $val) {
            $key = unserialize($key);
            $seq[] = [$key, $val];
        }
        return $seq;
    }

    function map($fn) {
        return new LinkedList(array_map($fn, $this->seq()));
    }

    function offsetGet($key) {
        $key = $this->hashCode($key);
        return $this->data[$key];
    }
    function offsetSet($key, $value) {
        $key = $this->hashCode($key);
        $this->data[$key] = $value;
    }
    function offsetExists($key) {
        $key = $this->hashCode($key);
        return isset($this->data[$key]);
    }
    function offsetUnset($key) {
        $key = $this->hashCode($key);
        unset($this->data[$key]);
    }
    function hashCode($val) {
        return serialize($val);
    }
}

class Set extends Collection {
    function init(array $data) {
        return array_unique($data);
    }
}

/** @api */
function symbol($name) {
    return Symbol::get($name);
}

/** @api */
function keyword($name) {
    return Keyword::get($name);
}

class Tag {
    public $name;

    function __construct($name) {
        $this->name = $name;
    }

    function __toString() {
        return $this->name;
    }
}

class Tagged {
    public $tag;
    public $value;

    function __construct(Tag $tag, $value) {
        $this->tag = $tag;
        $this->value = $value;
    }
}

class Discard {
}
