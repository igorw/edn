<?php

use igorw\edn\Symbol;
use igorw\edn\Keyword;
use igorw\edn\Map;
use igorw\edn\Set;
use igorw\edn\Tag;
use igorw\edn\Tagged;
use igorw\edn;

class ParserTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideEdn */
    function testParse($expected, $edn) {
        $data = igorw\edn\parse($edn);
        $this->assertEquals($expected, $data);
    }

    function provideEdn() {
        return [
            [[], ''],
            [[], ' '],
            [[], '   '],
            [[], ','],
            [[], ',,,'],
            [[], ',,, '],
            [[], ', ,,'],
            [[], ' , '],
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
            [['foo"'], '"foo\\""'],
            [['foo\\'], '"foo\\\\"'],
            [['c'], '\c'],
            [["\n", "\t", ' '], '\newline \tab \space'],
            [[Symbol::get('foo')], 'foo'],
            [[Symbol::get('foo'), Symbol::get('bar')], 'foo bar'],
            [[Symbol::get('foo/bar')], 'foo/bar'],
            [[Symbol::get('foo-bar')], 'foo-bar'],
            [[Symbol::get('/')], '/'],
            [[Symbol::get('ab#:cde')], 'ab#:cde'],
            [[Keyword::get('foo')], ':foo'],
            [[Keyword::get('foo'), Keyword::get('bar')], ':foo :bar'],
            [[Keyword::get('foo/bar')], ':foo/bar'],
            [[Keyword::get('foo-bar')], ':foo-bar'],
            [[Keyword::get('/')], ':/'],
            [[Keyword::get('ab#:cde')], ':ab#:cde'],
            [
                [
                    edn\create_list([
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
            [[edn\create_list([])], '()'],
            [[edn\create_list([Symbol::get('foo')])], '(foo)'],
            [[edn\create_list([Symbol::get('foo'), Symbol::get('bar')])], '(foo bar)'],
            [
                [
                    edn\create_list([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        edn\create_list([
                            Symbol::get('baz'),
                        ]),
                    ]),
                ],
                '(foo bar (baz))',
            ],
            [
                [
                    edn\create_list([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        edn\create_list([
                            Symbol::get('baz'),
                        ]),
                        Symbol::get('qux'),
                        edn\create_list([
                            edn\create_list([
                                Symbol::get('quux'),
                            ]),
                        ]),
                    ]),
                ],
                '(foo bar (baz) qux ((quux)))',
            ],
            [[edn\create_vector([])], '[]'],
            [[edn\create_vector([Symbol::get('foo')])], '[foo]'],
            [[edn\create_vector([Symbol::get('foo'), Symbol::get('bar')])], '[foo bar]'],
            [
                [
                    edn\create_vector([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        edn\create_vector([
                            Symbol::get('baz')
                        ]),
                    ]),
                ],
                '[foo bar [baz]]',
            ],
            [[edn\create_map([])], '{}'],
            [[edn\create_map([Keyword::get('foo'), Symbol::get('bar')])], '{:foo bar}'],
            [[edn\create_map([Keyword::get('foo'), edn\create_list([Symbol::get('bar')])])], '{:foo (bar)}'],
            [[edn\create_set([])], '#{}'],
            [[edn\create_set([Symbol::get('foo')])], '#{foo}'],
            [[edn\create_set([Symbol::get('foo'), Symbol::get('bar')])], '#{foo bar}'],
            [
                [
                    edn\create_set([
                        edn\create_list([
                            Symbol::get('foo'),
                            Symbol::get('bar'),
                        ]),
                    ]),
                ],
                '#{(foo bar)}',
            ],
            [
                [
                    edn\create_set([
                        edn\create_map([
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
                    edn\create_map([
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
                        edn\create_list([
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
                        edn\create_map([
                            Keyword::get('first'),
                            'Fred',
                            Keyword::get('last'),
                            'Mertz',
                        ])
                    ),
                ],
                '#myapp/Person {:first "Fred" :last "Mertz"}',
            ],
            [[new Tagged(new Tag('ab#:cde'), 'foo')], '#ab#:cde "foo"'],
            [[], ';'],
            [[], '; foo'],
            [[Symbol::get('foo')], "; foo\nfoo"],
            [[Symbol::get('foo'), Symbol::get('bar')], "; foo\nfoo; bar\nbar"],
            [[], "; foo bar baz qux"],
            [[], "; foo bar baz qux\n"],
            [[], "; foo bar baz qux\n\n"],
            [[Symbol::get('quux')], "; foo bar baz qux\n\nquux\n\n"],
            [[], '#_foo'],
            [[edn\create_vector([Symbol::get('a'), Symbol::get('b'), 42])], '[a b #_foo 42]'],
            [[], '#_ foo'],
            [[edn\create_vector([Symbol::get('a'), Symbol::get('b'), 42])], '[a b #_ foo 42]'],
            [['#_ foo'], '"#_ foo"'],
        ];
    }

    function testParseWithTagHandler() {
        $expected = [new Person('Fred', 'Mertz')];
        $edn = '#myapp/Person {:first "Fred" :last "Mertz"}';

        $data = igorw\edn\parse($edn, [
            'myapp/Person' => function ($node) {
                return new Person(
                    $node[Keyword::get('first')],
                    $node[Keyword::get('last')]
                );
            },
        ]);

        $this->assertEquals($expected, $data);
    }

    function testParseWithInstTagHandler() {
        $edn = '#inst "1985-04-12T23:20:50.52Z"';

        $data = igorw\edn\parse($edn, [
            'inst' => function ($node) {
                return new \DateTime($node);
            },
        ]);

        $this->assertEquals('1985-04-12 23:20:50', $data[0]->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     * @dataProvider provideUnmatchedParensEdn
     * @expectedException igorw\edn\ParserException
     */
    function parseShouldRejectUnmatchedParens($edn) {
        igorw\edn\parse($edn);
    }

    function provideUnmatchedParensEdn() {
        return [
            ['{'],
            ['}'],
            ['{{'],
            ['}}'],
            ['{ "foo"'],
            ['{ "foo" :bar'],
            ['foo bar :baz}'],
            ['[}'],
            ['#{{[}}'],
            [':foo [#{:what)}]'],
        ];
    }
}

class Person {
    public $firstName;
    public $lastName;

    function __construct($firstName, $lastName) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
