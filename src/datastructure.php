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

/** @api */
function symbol($name) {
    return Symbol::get($name);
}

/** @api */
function keyword($name) {
    return Keyword::get($name);
}

class Comment {
}

class Discard {
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
