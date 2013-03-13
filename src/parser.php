<?php

namespace igorw\edn;

use Phlexy\LexerFactory\Stateless\UsingPregReplace;
use Phlexy\LexerDataGenerator;

function parse($edn) {
    $tokens = tokenize($edn);

    return parse_tokens($tokens, $edn);
}

function tokenize($edn) {
    $factory = new UsingPregReplace(new LexerDataGenerator());

    $lexer = $factory->createLexer(array(
        'nil|true|false'                 => 'literal',
        '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' => 'string',
        '[\s,]'                          => 'whitespace',
        '\\\\[a-z]+'                     => 'character',
        get_symbol_regex()               => 'symbol',
        ':(?:'.get_symbol_regex().')'    => 'keyword',
        '(?:[+-]?)(?:[0-9]+\.[0-9]+)M?'  => 'float',
        '(?:[+-]?)(?:[0-9]+)N?'          => 'int',
        '\\('                            => 'list_start',
        '\\)'                            => 'list_end',
        '\\['                            => 'vector_start',
        '\\]'                            => 'vector_end',
        '\\{'                            => 'map_start',
        '\\}'                            => 'map_end',
    ));

    $tokens = $lexer->lex($edn);

    return $tokens;
}

function parse_tokens(array $tokens, $edn) {
    $ast = [];

    $tokens = array_values(array_filter($tokens, function ($token) {
        return 'whitespace' !== $token[0];
    }));

    $i = 0;
    $size = count($tokens);

    while ($i < $size) {
        $listTypes = [
            'list_start'    => ['list_start', 'list_end', __NAMESPACE__.'\\EdnList'],
            'vector_start'  => ['vector_start', 'vector_end', __NAMESPACE__.'\\Vector'],
            'map_start'  => ['map_start', 'map_end', __NAMESPACE__.'\\Map'],
        ];

        $type = $tokens[$i][0];
        if (isset($listTypes[$type])) {
            $result = parse_subtree($listTypes[$type], $tokens, $i, $edn);
            $ast[] = $result['subtree'];
            $i = $result['i'];

            continue;
        }

        $ast[] = parse_token($tokens[$i++]);
    }

    return $ast;
}

function parse_subtree($listType, array $tokens, $i, $edn) {
    list($startToken, $endToken, $dataClass) = $listType;

    $subtree = null;
    $level = 0;
    $j = 0;

    foreach (array_slice($tokens, $i) as $j => $token) {
        if ($startToken === $token[0]) {
            $level++;
        }

        if ($endToken === $token[0]) {
            $level--;
        }

        if (0 === $level) {
            $subtree = new $dataClass(parse_tokens(array_slice($tokens, $i+1, $j-1), $edn));
            break;
        }
    }

    return [
        'subtree'   => $subtree,
        'i'         => $i+$j+1,
    ];
}

function parse_token($token) {
    list($type, $_, $edn) = $token;

    switch ($type) {
        case 'literal':
            return resolve_literal($edn);
        case 'string':
            return resolve_string(substr($edn, 1, -1));
        case 'character':
            return resolve_character(substr($edn, 1));
        case 'symbol':
            return new Symbol($edn);
        case 'keyword':
            return new Keyword(substr($edn, 1));
        case 'int':
            return resolve_int($edn);
        case 'float':
            return resolve_float($edn);
    }

    throw new ParserException(sprintf('Could not parse input %s.', $edn));
}

function resolve_literal($edn) {
    switch ($edn) {
        case 'nil':
            return null;
        case 'true':
            return true;
        case 'false':
            return false;
    }

    throw new ParserException(sprintf('Could not parse input %s as litral.', $edn));
}

function resolve_string($edn) {
    return strtr($edn, [
        '\t' => "\t",
        '\r' => "\r",
        '\n' => "\n",
    ]);
}

function resolve_character($edn) {
    $chars = [
        'newline'   => "\n",
        'return'    => "\r",
        'space'     => ' ',
        'tab'       => "\t",
    ];

    return isset($chars[$edn]) ? $chars[$edn] : $edn;
}

function get_symbol_regex() {
    $alpha = 'a-zA-Z';
    $alphaNum = 'a-zA-Z0-9';
    $chars = '*!_?$%&=';
    $extraChars = '.+-';

    $nonNumericRegex = "[$extraChars][$alpha$chars]+|[$alpha$chars][$alphaNum$chars$extraChars]*";

    $symbolRegex = [];
    $symbolRegex[] = "/{0}[$alpha$chars][$alphaNum$chars]*/(?:$nonNumericRegex)/{0}";
    $symbolRegex[] = $nonNumericRegex;
    $symbolRegex[] = '/';

    return implode('|', $symbolRegex);
}

function resolve_int($edn) {
    if (preg_match('#^([+-]?)([0-9]+)N?$#', $edn, $matches)) {
        $factor = ($matches[1] && '-' === $matches[1]) ? -1 : 1;
        return (int) $matches[2] * $factor;
    }

    throw new ParserException(sprintf('Could not parse input %s as int.', $edn));
}

function resolve_float($edn) {
    if (preg_match('#^([+-]?)([0-9]+\.[0-9]+)M?$#', $edn, $matches)) {
        $factor = ($matches[1] && '-' === $matches[1]) ? -1 : 1;
        return (float) $matches[2] * $factor;
    }

    throw new ParserException(sprintf('Could not parse input %s as float.', $edn));
}
