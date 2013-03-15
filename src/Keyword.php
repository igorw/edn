<?php

namespace igorw\edn;

/** @api */
class Keyword extends Symbol {
    static public $symbols = [];

    function __toString() {
        return ':'.$this->value;
    }
}
