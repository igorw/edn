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
