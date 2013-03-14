<?php

namespace igorw\edn;

use Ardent\LinkedList;
use Ardent\Vector;
use Ardent\Map;
use Ardent\Set;

function encode(array $ast) {
    return implode(' ', array_map(__NAMESPACE__.'\\encode_node', $ast));
}

function encode_node($node) {
    $literals = [
        null => 'nil',
        true => 'true',
        false => 'false',
    ];

    if (is_literal($node) && isset($literals[$node])) {
        return $literals[$node];
    }

    if (is_character($node)) {
        return encode_character($node);
    }

    if (is_string($node)) {
        return encode_string($node);
    }

    if ($node instanceof Keyword) {
        return ':'.encode_symbol($node);
    }

    if ($node instanceof Symbol) {
        return encode_symbol($node);
    }

    if (is_int($node) || is_float($node)) {
        return $node;
    }

    if ($node instanceof LinkedList) {
        return encode_list($node);
    }

    if ($node instanceof Vector) {
        return encode_vector($node);
    }

    if ($node instanceof Map) {
        return encode_map($node);
    }

    if ($node instanceof Set) {
        return encode_set($node);
    }

    if ($node instanceof Tagged) {
        return encode_tagged($node);
    }

    if (is_array($node)) {
        return encode_list(new \ArrayIterator($node));
    }

    throw new \InvalidArgumentException(sprintf('Cannot parse node of type %s.', gettype($node)));
}

function is_literal($node) {
    return is_null($node) || is_bool($node);
}

function is_character($node) {
    return is_string($node) && (bool) preg_match('#^[a-z\n\r\t ]$#', $node);
}

function encode_character($char) {
    $map = [
        "\n" => '\newline',
        "\r" => '\return',
        " "  => '\space',
        "\t" => '\tab',
    ];

    return isset($map[$char]) ? $map[$char] : "\\$char";
}

function encode_string($string) {
    return '"'.escape_string($string).'"';
}

function escape_string($string) {
    return strtr($string, [
        "\n" => '\n',
        "\r" => '\r',
        "\t" => '\t',
        '"' => '\\"',
    ]);
}

function encode_symbol(Symbol $symbol) {
    return $symbol->value;
}

function encode_list($list) {
    return '('.encode(iterator_to_array($list)).')';
}

function encode_vector($vector) {
    return '['.encode(iterator_to_array($vector)).']';
}

function encode_map($map) {
    return '{'.encode_map_elements($map).'}';
}

function encode_map_elements($map) {
    $encoded = [];
    foreach ($map as $key => $value) {
        // todo: fix issue with object keys in ardent's HashMapIterator
        // $encoded[] = encode_node($key).' '.encode_node($value);
        $encoded[] = $key.' '.encode_node($value);
    }
    return implode(' ', $encoded);
}

function encode_set($set) {
    return '#{'.encode(iterator_to_array($set)).'}';
}

function encode_tagged($tagged) {
    return '#'.$tagged->tag->name.' '.encode_node($tagged->value);
}
