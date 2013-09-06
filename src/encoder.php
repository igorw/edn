<?php

namespace igorw\edn;

/** @api */
function encode($ast) {
    if (!is_array($ast)) {
        $ast = [$ast];
    }

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
        return !count($node) || isset($node[0])
            ? encode_list(new \ArrayIterator($node))
            : encode_map(convert_assoc_to_map($node));
    }

    throw new \InvalidArgumentException(sprintf('Cannot encode node of type %s.', gettype($node)));
}

function is_literal($node) {
    return is_null($node) || is_bool($node);
}

function is_character($node) {
    return is_string($node) && (bool) preg_match('#^[a-z\n\r\t\f ]$#', $node);
}

function encode_character($char) {
    $map = [
        "\n" => '\newline',
        "\r" => '\return',
        " "  => '\space',
        "\t" => '\tab',
        "\f" => '\formfeed',
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
        '"'  => '\\"',
        '\\' => '\\\\',
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
    $encoded = $map->map(function ($item) {
        list($key, $val) = $item;
        return encode_node($key).' '.encode_node($val);
    });
    return implode(' ', iterator_to_array($encoded, false));
}

function encode_set($set) {
    return '#{'.encode(iterator_to_array($set)).'}';
}

function encode_tagged($tagged) {
    return '#'.$tagged->tag->name.' '.encode_node($tagged->value);
}

function convert_assoc_to_map($assoc) {
    $map = new Map();
    foreach ($assoc as $key => $value) {
        $map[Keyword::get($key)] = $value;
    }
    return $map;
}
