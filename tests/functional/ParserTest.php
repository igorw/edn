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
            [[new Symbol('foo')], 'foo'],
            [[new Symbol('foo'), new Symbol('bar')], 'foo bar'],
            [[new Symbol('foo/bar')], 'foo/bar'],
            [[new Symbol('foo-bar')], 'foo-bar'],
            [[new Symbol('/')], '/'],
            [[new Keyword('foo')], ':foo'],
            [[new Keyword('foo'), new Keyword('bar')], ':foo :bar'],
            [[new Keyword('foo/bar')], ':foo/bar'],
            [[new Keyword('foo-bar')], ':foo-bar'],
            [[new Keyword('/')], ':/'],
            [
                [
                    new EdnList([
                        new Symbol('defproject'),
                        new Symbol('com.thortech/data.edn'),
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
            [[new EdnList([new Symbol('foo')])], '(foo)'],
            [[new EdnList([new Symbol('foo'), new Symbol('bar')])], '(foo bar)'],
            [
                [
                    new EdnList([
                        new Symbol('foo'),
                        new Symbol('bar'),
                        new EdnList([
                            new Symbol('baz'),
                        ]),
                    ]),
                ],
                '(foo bar (baz))',
            ],
            [
                [
                    new EdnList([
                        new Symbol('foo'),
                        new Symbol('bar'),
                        new EdnList([
                            new Symbol('baz'),
                        ]),
                        new Symbol('qux'),
                        new EdnList([
                            new EdnList([
                                new Symbol('quux'),
                            ]),
                        ]),
                    ]),
                ],
                '(foo bar (baz) qux ((quux)))',
            ],
            [[new Vector([])], '[]'],
            [[new Vector([new Symbol('foo')])], '[foo]'],
            [[new Vector([new Symbol('foo'), new Symbol('bar')])], '[foo bar]'],
            [
                [
                    new Vector([
                        new Symbol('foo'),
                        new Symbol('bar'),
                        new Vector([
                            new Symbol('baz')
                        ]),
                    ]),
                ],
                '[foo bar [baz]]',
            ],
            [[new Map([])], '{}'],
            [[new Map([new Keyword('foo'), new Symbol('bar')])], '{:foo bar}'],
            [[new Map([new Keyword('foo'), new EdnList([new Symbol('bar')])])], '{:foo (bar)}'],
            [[new Set([])], '#{}'],
            [[new Set([new Symbol('foo')])], '#{foo}'],
            [[new Set([new Symbol('foo'), new Symbol('bar')])], '#{foo bar}'],
            [
                [
                    new Set([
                        new EdnList([
                            new Symbol('foo'),
                            new Symbol('bar'),
                        ]),
                    ]),
                ],
                '#{(foo bar)}',
            ],
            [
                [
                    new Set([
                        new Map([
                            new Keyword('foo'),
                            new Symbol('bar'),
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
                        new Keyword('first'),
                        'Fred',
                        new Keyword('last'),
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
                            new Keyword('foo'),
                            new Keyword('bar'),
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
                            new Keyword('first'),
                            'Fred',
                            new Keyword('last'),
                            'Mertz',
                        ])
                    ),
                ],
                '#myapp/Person {:first "Fred" :last "Mertz"}',
            ],
            [[], ';'],
            [[], '; foo'],
            [[new Symbol('foo')], "; foo\nfoo"],
            [[new Symbol('foo'), new Symbol('bar')], "; foo\nfoo; bar\nbar"],
            [[], "; foo bar baz qux"],
            [[], "; foo bar baz qux\n"],
            [[], "; foo bar baz qux\n\n"],
            [[new Symbol('quux')], "; foo bar baz qux\n\nquux\n\n"],
            [[], '#_foo'],
            [[new Vector([new Symbol('a'), new Symbol('b'), 42])], '[a b #_foo 42]'],
            [[], '#_ foo'],
            [[new Vector([new Symbol('a'), new Symbol('b'), 42])], '[a b #_ foo 42]'],
        ];
    }
}
