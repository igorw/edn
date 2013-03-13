<?php

namespace igorw\edn;

class Keyword {
    public $value;

    function __construct($value) {
        $this->value = $value;
    }

    function __toString() {
        return $this->value;
    }
}
