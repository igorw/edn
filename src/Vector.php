<?php

namespace igorw\edn;

class Vector implements \ArrayAccess {
    public $data;

    function __construct(array $data) {
        $this->data = $data;
    }

    function offsetGet($index) {
        return $this->data[$index];
    }

    function offsetSet($index, $value) {
        $this->data[$index] = $value;
    }

    function offsetUnset($index) {
        unset($this->data[$index]);
    }

    function offsetExists($index) {
        return isset($this->data[$index]);
    }
}
