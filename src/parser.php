<?php

namespace igorw\edn;

use Phlexy\LexerFactory\Stateless\UsingPregReplace;
use Phlexy\LexerDataGenerator;
use Phlexy\LexingException;

/** @api */
function parse($edn, array $tagHandlers = []) {
    $tagHandlers = array_merge(default_tag_handlers(), $tagHandlers);

    $tokens = try_tokenize($edn);
    $ast = parse_tokens($tokens, $edn);
    $ast = apply_tag_handlers($ast, $tagHandlers);

    return $ast;
}

/** @api */
class ParserException extends \InvalidArgumentException {
}

function default_tag_handlers() {
    return [
        'inst' => function ($node) {
            return new \DateTime($node);
        },
        'uuid' => ['Rhumsaa\Uuid\Uuid', 'fromString'],
    ];
}

function try_tokenize($edn) {
    try {
        return tokenize($edn);
    } catch (LexingException $e) {
        throw new ParserException(sprintf('Could not lex input %s', $edn), 0, $e);
    }
}

function tokenize($edn) {
    $factory = new UsingPregReplace(new LexerDataGenerator());

    $delim = function ($pattern) {
        return "(?:$pattern)(?=[\s,\\)\\]\\};]|$)";
    };

    $lexer = $factory->createLexer([
        ';(?:.*)(?:\\n)?'                           => 'comment',
        '#_[\s,]?'                                  => 'discard',
        $delim('nil|true|false')                    => 'literal',
        $delim('"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"')    => 'string',
        '[\s,]'                                     => 'whitespace',
        $delim('\\\\(?:newline|return|space|tab|formfeed|.)')   => 'character',
        $delim('(?:[+-])?\d+N?')                                => 'int',
        $delim('(?:[+-])?\d+(\.\d+)?(?:[eE][-+]?\d+)?M?')       => 'float',
        $delim(get_symbol_regex())                  => 'symbol',
        $delim(':(?:'.get_symbol_regex().')')       => 'keyword',
        $delim('#(?:'.get_symbol_regex().')')       => 'tag',
        '\\('                                       => 'list_start',
        '\\)'                                       => 'list_end',
        '\\['                                       => 'vector_start',
        '\\]'                                       => 'vector_end',
        '#\\{'                                      => 'set_start',
        '\\{'                                       => 'map_start',
        '\\}'                                       => 'map_set_end',
    ]);

    $tokens = $lexer->lex($edn);

    return $tokens;
}

function parse_tokens(array $tokens, $edn) {
    $classes = [
        'list_start'    => __NAMESPACE__.'\\LinkedList',
        'vector_start'  => __NAMESPACE__.'\\Vector',
        'map_start'     => __NAMESPACE__.'\\Map',
        'set_start'     => __NAMESPACE__.'\\Set',
    ];

    $ast = [];

    $tokens = array_values(array_filter($tokens, function ($token) {
        return !in_array(token_type($token), ['whitespace', 'comment']);
    }));

    $i = 0;
    $size = count($tokens);

    while ($i < $size) {
        $type = token_type($tokens[$i]);
        if (isset($classes[$type])) {
            $result = parse_subtree($classes[$type], $tokens, $i, $edn);
            $ast[] = $result['subtree'];
            $i = $result['i'];

            continue;
        }

        $ast[] = parse_token($tokens[$i++]);
    }

    $ast = strip_discards($ast);
    $ast = wrap_tags($ast);

    return $ast;
}

function parse_subtree($class, array $tokens, $i, $edn) {
    $subtree = null;
    $level = [];
    $j = 0;

    foreach (array_slice($tokens, $i) as $j => $token) {
        if (token_is_start_type($token)) {
            $level[] = token_type($token);
        }

        if (token_is_end_type($token)) {
            $matchingStartType = array_pop($level);
            if (token_matches_start_type($token, $matchingStartType)) {
                throw new ParserException(sprintf('Invalid matching parens in input %s.', $edn));
            }
        }

        if (0 === count($level)) {
            $subtree = new $class(parse_tokens(array_slice($tokens, $i+1, $j-1), $edn));
            break;
        }
    }

    if (0 !== count($level)) {
        throw new ParserException(sprintf('Unmatched parens in input %s.', $edn));
    }

    return [
        'subtree'   => $subtree,
        'i'         => $i+$j+1,
    ];
}

function token_is_start_type(array $token) {
    $startTypes = ['list_start', 'vector_start', 'map_start', 'set_start'];

    return in_array(token_type($token), $startTypes, true);
}

function token_is_end_type(array $token) {
    $endTypes = ['list_end', 'vector_end', 'map_set_end'];

    return in_array(token_type($token), $endTypes, true);
}

function token_matches_start_type(array $token, $startType) {
    $matchingTypes = [
        'list_start'    => 'list_end',
        'vector_start'  => 'vector_end',
        'map_start'     => 'map_set_end',
        'set_start'     => 'map_set_end',
    ];

    return token_type($token) !== $matchingTypes[$startType];
}

function token_type(array $token) {
    return $token[0];
}

function parse_token(array $token) {
    list($type, $_, $edn) = $token;

    switch ($type) {
        case 'literal':
            return resolve_literal($edn);
        case 'string':
            return resolve_string(substr($edn, 1, -1));
        case 'character':
            return resolve_character(substr($edn, 1));
        case 'symbol':
            return Symbol::get($edn);
        case 'keyword':
            return Keyword::get(substr($edn, 1));
        case 'tag':
            return new Tag(substr($edn, 1));
        case 'int':
            return resolve_int($edn);
        case 'float':
            return resolve_float($edn);
        case 'discard':
            return new Discard();
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
        '\t'    => "\t",
        '\r'    => "\r",
        '\n'    => "\n",
        '\"'    => "\"",
        '\\\\'  => "\\",
    ]);
}

function resolve_character($edn) {
    $chars = [
        'newline'   => "\n",
        'return'    => "\r",
        'space'     => ' ',
        'tab'       => "\t",
        'formfeed'  => "\f",
    ];

    return isset($chars[$edn]) ? $chars[$edn] : $edn;
}

function get_symbol_regex() {
    $part = "(?:[a-zA-Z*!_?$%&=]|[-+.][a-zA-Z.*+!_?$%&=:#-])[a-zA-Z0-9.*+!_?$%&=:#-]*";
    $part = "/(?!/)|(?<!/)$part(?:/$part)?(?!/)(?!$part)";
    $part = "(?:$part)|[-+](?!\d)|(?<!\d)\.(?!\d)";
    return $part;
}

function resolve_int($edn) {
    return (int) $edn;
}

function resolve_float($edn) {
    return (float) $edn;
}

function strip_discards(array $ast) {
    $discard = false;

    foreach ($ast as $i => $node) {
        if ($node instanceof Discard) {
            unset($ast[$i]);
            $discard = true;

            continue;
        }

        if ($discard) {
            unset($ast[$i]);
            $discard = false;
        }
    }

    return array_values($ast);
}

function wrap_tags(array $ast) {
    $tag = null;

    foreach ($ast as $i => $node) {
        if ($node instanceof Tag) {
            $tag = $node;
            unset($ast[$i]);

            continue;
        }

        if ($tag) {
            $ast[$i] = new Tagged($tag, $node);
            $tag = null;
        }
    }

    return array_values($ast);
}

function apply_tag_handlers(array $ast, array $tagHandlers) {
    if (!$tagHandlers) {
        return $ast;
    }

    foreach ($ast as $i => $node) {
        $ast[$i] = apply_tag_handlers_node($node, $tagHandlers);
    }

    return $ast;
}

function apply_tag_handlers_node($node, array $tagHandlers) {
    if ($node instanceof Tagged && isset($tagHandlers[$node->tag->name])) {
        $handler = $tagHandlers[$node->tag->name];
        $node = $handler($node->value);
    }

    if ($node instanceof Collection) {
        $filter = function ($value) use ($tagHandlers) {
            return apply_tag_handlers_node($value, $tagHandlers);
        };

        if ($node instanceof Map) {
            $node = $node->map(function ($item) use ($filter) {
                list($key, $value) = $item;
                return [$filter($key), $filter($value)];
            });
            $node = seq_to_map($node);
        } else {
            $node = $node->map($filter);
        }
    }

    return $node;
}

function seq_to_map(Collection $seq) {
    $map = [];
    foreach ($seq as $item) {
        list($key, $value) = $item;
        $map[] = $key;
        $map[] = $value;
    }
    return new Map($map);
}
