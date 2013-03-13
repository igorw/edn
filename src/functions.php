<?php

namespace igorw\edn;

function parse($sourceEdn) {
    $parts = split_by_whitespace($sourceEdn);

    return array_map(__NAMESPACE__.'\\parse_part', $parts);
}

// http://stackoverflow.com/a/11873272/289985
function split_by_whitespace($sourceEdn) {
    return array_values(
        array_filter(
            preg_split('#(?=(?:[^"]*"[^"]*")*[^"]*$)[\s,]#', $sourceEdn),
            function ($value) { return $value !== ''; }));
}

function parse_part($edn) {
    switch ($edn) {
        case 'nil':
            return null;
        case 'true':
            return true;
        case 'false':
            return false;
    }

    if ('"' === $edn[0]) {
        return resolve_escape_characters(substr($edn, 1, -1));
    }

    if ('\\' === $edn[0]) {
        return resolve_character(substr($edn, 1));
    }

    if (is_symbol($edn)) {
        return new Symbol($edn);
    }

    if (':' === $edn[0] && is_symbol(substr($edn, 1))) {
        return new Keyword(substr($edn, 1));
    }

    if (preg_match('#^([+-]?)([0-9]+)N?$#', $edn, $matches)) {
        $factor = ($matches[1] && '-' === $matches[1]) ? -1 : 1;
        return (int) $matches[2] * $factor;
    }

    if (preg_match('#^([+-]?)([0-9]+\.[0-9]+)M?$#', $edn, $matches)) {
        $factor = ($matches[1] && '-' === $matches[1]) ? -1 : 1;
        return (float) $matches[2] * $factor;
    }

    throw new ParserException(sprintf('Could not parse input %s.', $edn));
}

function resolve_escape_characters($edn) {
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

function is_symbol($edn) {
    $alpha = 'a-zA-Z';
    $alphaNum = 'a-zA-Z0-9';
    $chars = '*!_?$%&=';
    $extraChars = '.+-';

    $nonNumericRegex = "[$extraChars][$alpha$chars]+|[$alpha$chars][$alphaNum$chars$extraChars]*";

    $symbolRegex = $nonNumericRegex;
    $symbolRegex .= "|/";
    $symbolRegex .= "|/{0}[$alpha$chars][$alphaNum$chars]*/($nonNumericRegex)/{0}";

    return (bool) preg_match("#^($symbolRegex)$#", $edn);
}
