<?php

namespace igorw\edn;

class EdnList implements \IteratorAggregate {
    public $data;

    function __construct(array $data) {
        $this->data = $data;
    }

    function getIterator() {
        return new \ArrayIterator($this->data);
    }
}
