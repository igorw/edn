<?php

use igorw\edn\Symbol;
use igorw\edn\Keyword;
use igorw\edn\EdnList;
use igorw\edn\Vector;
use igorw\edn\Map;
use igorw\edn\Set;
use igorw\edn\Tag;
use igorw\edn\Tagged;

class ParserTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideEdn */
    public function testParse($expected, $sourceEdn) {
        $data = igorw\edn\parse($sourceEdn);
        $this->assertEquals($expected, $data);
    }

    public function provideEdn() {
        return [
            [[], ''],
            [[null], 'nil'],
            [[true], 'true'],
            [[false], 'false'],
            [[true, false], 'true false'],
            [[true, false], 'true, false'],
            [['foobar'], '"foobar"'],
            [['foo', 'bar'], '"foo", "bar"'],
            [["foo\nbar"], '"foo\nbar"'],
            [["foo\tbar"], '"foo\tbar"'],
            [["GET /foo HTTP/1.1\r\n"], '"GET /foo HTTP/1.1\r\n"'],
            [['c'], '\c'],
            [["\n", "\t", ' '], '\newline \tab \space'],
            [[Symbol::get('foo')], 'foo'],
            [[Symbol::get('foo'), Symbol::get('bar')], 'foo bar'],
            [[Symbol::get('foo/bar')], 'foo/bar'],
            [[Symbol::get('foo-bar')], 'foo-bar'],
            [[Symbol::get('/')], '/'],
            [[Keyword::get('foo')], ':foo'],
            [[Keyword::get('foo'), Keyword::get('bar')], ':foo :bar'],
            [[Keyword::get('foo/bar')], ':foo/bar'],
            [[Keyword::get('foo-bar')], ':foo-bar'],
            [[Keyword::get('/')], ':/'],
            [
                [
                    new EdnList([
                        Symbol::get('defproject'),
                        Symbol::get('com.thortech/data.edn'),
                        "0.1.0-SNAPSHOT",
                    ]),
                ],
                '(defproject com.thortech/data.edn "0.1.0-SNAPSHOT")',
            ],
            [[1], '1'],
            [[-1], '-1'],
            [[1], '+1'],
            [[0], '0'],
            [[0], '-0'],
            [[0], '+0'],
            [[10], '10'],
            [[20], '20'],
            [[200], '200'],
            [[-200], '-200'],
            [[42], '42'],
            [[1.0], '1.0'],
            [[1.2], '1.2'],
            [[-1.2], '-1.2'],
            [[-0.0], '-0.0'],
            [[-0.25], '-0.25'],
            [[new EdnList([])], '()'],
            [[new EdnList([Symbol::get('foo')])], '(foo)'],
            [[new EdnList([Symbol::get('foo'), Symbol::get('bar')])], '(foo bar)'],
            [
                [
                    new EdnList([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        new EdnList([
                            Symbol::get('baz'),
                        ]),
                    ]),
                ],
                '(foo bar (baz))',
            ],
            [
                [
                    new EdnList([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        new EdnList([
                            Symbol::get('baz'),
                        ]),
                        Symbol::get('qux'),
                        new EdnList([
                            new EdnList([
                                Symbol::get('quux'),
                            ]),
                        ]),
                    ]),
                ],
                '(foo bar (baz) qux ((quux)))',
            ],
            [[new Vector([])], '[]'],
            [[new Vector([Symbol::get('foo')])], '[foo]'],
            [[new Vector([Symbol::get('foo'), Symbol::get('bar')])], '[foo bar]'],
            [
                [
                    new Vector([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        new Vector([
                            Symbol::get('baz')
                        ]),
                    ]),
                ],
                '[foo bar [baz]]',
            ],
            [[new Map([])], '{}'],
            [[new Map([Keyword::get('foo'), Symbol::get('bar')])], '{:foo bar}'],
            [[new Map([Keyword::get('foo'), new EdnList([Symbol::get('bar')])])], '{:foo (bar)}'],
            [[new Set([])], '#{}'],
            [[new Set([Symbol::get('foo')])], '#{foo}'],
            [[new Set([Symbol::get('foo'), Symbol::get('bar')])], '#{foo bar}'],
            [
                [
                    new Set([
                        new EdnList([
                            Symbol::get('foo'),
                            Symbol::get('bar'),
                        ]),
                    ]),
                ],
                '#{(foo bar)}',
            ],
            [
                [
                    new Set([
                        new Map([
                            Keyword::get('foo'),
                            Symbol::get('bar'),
                        ]),
                    ]),
                ],
                '#{{:foo bar}}',
            ],
            [
                [
                    new Tagged(new Tag('myapp/Person'), 'foo'),
                ],
                '#myapp/Person "foo"',
            ],
            [
                [
                    new Map([
                        Keyword::get('first'),
                        'Fred',
                        Keyword::get('last'),
                        'Mertz',
                    ]),
                ],
                '{:first "Fred" :last "Mertz"}',
            ],
            [
                [
                    new Tagged(
                        new Tag('myapp/Person'),
                        new EdnList([
                            Keyword::get('foo'),
                            Keyword::get('bar'),
                        ])
                    ),
                ],
                '#myapp/Person (:foo :bar)',
            ],
            [
                [
                    new Tagged(
                        new Tag('myapp/Person'),
                        new Map([
                            Keyword::get('first'),
                            'Fred',
                            Keyword::get('last'),
                            'Mertz',
                        ])
                    ),
                ],
                '#myapp/Person {:first "Fred" :last "Mertz"}',
            ],
            [[], ';'],
            [[], '; foo'],
            [[Symbol::get('foo')], "; foo\nfoo"],
            [[Symbol::get('foo'), Symbol::get('bar')], "; foo\nfoo; bar\nbar"],
            [[], "; foo bar baz qux"],
            [[], "; foo bar baz qux\n"],
            [[], "; foo bar baz qux\n\n"],
            [[Symbol::get('quux')], "; foo bar baz qux\n\nquux\n\n"],
            [[], '#_foo'],
            [[new Vector([Symbol::get('a'), Symbol::get('b'), 42])], '[a b #_foo 42]'],
            [[], '#_ foo'],
            [[new Vector([Symbol::get('a'), Symbol::get('b'), 42])], '[a b #_ foo 42]'],
        ];
    }
}
