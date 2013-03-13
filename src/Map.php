<?php

namespace igorw\edn;

class Map implements \ArrayAccess {
    public $data;

    function __construct(array $data) {
        $this->data = $this->normalize($data);
    }

    function offsetGet($key) {
        $key = $this->serializeKey($key);
        return $this->data[$key];
    }

    function offsetSet($key, $value) {
        $key = $this->serializeKey($key);
        $this->data[$key] = $value;
    }

    function offsetUnset($key) {
        $key = $this->serializeKey($key);
        unset($this->data[$key]);
    }

    function offsetExists($key) {
        $key = $this->serializeKey($key);
        return isset($this->data[$key]);
    }

    private function normalize(array $data) {
        $normalized = [];

        $prev = null;
        foreach ($data as $value) {
            if (!$prev) {
                $prev = $value;
                continue;
            }

            $normalized[$this->serializeKey($prev)] = $value;
            $prev = null;
        }

        return $normalized;
    }

    private function serializeKey($key) {
        return serialize($key);
    }
}
