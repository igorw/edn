<?php

namespace igorw\edn;

class Tag {
    public $name;

    function __construct($name) {
        $this->name = $name;
    }

    function __toString() {
        return $this->name;
    }
}
