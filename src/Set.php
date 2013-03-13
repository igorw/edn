<?php

namespace igorw\edn;

class Set {
    public $data;

    function __construct(array $data) {
        $this->data = $data;
    }

    function contains($value) {
        return false !== array_search($value, $this->data, true);
    }

    function add($value) {
        if ($this->contains($value)) {
            throw new \InvalidArgumentException(sprintf('Provided value %s exists in the set already.', $value));
        }

        $this->data[] = $value;
    }

    function remove($value) {
        $index = array_search($value, $this->data, true);

        if (false === $index) {
            throw new \InvalidArgumentException(sprintf('Provided value %s does not exist in the set.', $value));
        }

        unset($this->data[$index]);
    }
}
