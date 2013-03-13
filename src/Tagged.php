<?php

namespace igorw\edn;

class Tagged {
    public $tag;
    public $value;

    function __construct(Tag $tag, $value) {
        $this->tag = $tag;
        $this->value = $value;
    }
}
