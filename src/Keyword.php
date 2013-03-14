<?php

namespace igorw\edn;

class Keyword extends Symbol {
    static public $symbols = [];

    function __toString() {
        return ':'.$this->value;
    }
}
