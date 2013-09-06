<?php

use igorw\edn\Symbol;
use igorw\edn\Keyword;
use igorw\edn\LinkedList;
use igorw\edn\Vector;
use igorw\edn\Map;
use igorw\edn\Set;
use igorw\edn\Tag;
use igorw\edn\Tagged;
use igorw\edn;

class EncoderTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideAst */
    function testParse($expected, $ast) {
        $edn = igorw\edn\encode($ast);
        $this->assertEquals($expected, $edn);
    }

    function provideAst() {
        return [
            ['', []],
            ['nil', [null]],
            ['true', [true]],
            ['false', [false]],
            ['true false', [true, false]],
            ['"foobar"', ['foobar']],
            ['"foo" "bar"', ['foo', 'bar']],
            ['"foo\nbar"', ["foo\nbar"]],
            ['"foo\tbar"', ["foo\tbar"]],
            ['"GET /foo HTTP/1.1\r\n"', ["GET /foo HTTP/1.1\r\n"]],
            ['"foo\\""', ['foo"']],
            ['\c', ['c']],
            ['\newline \tab \space', ["\n", "\t", ' ']],
            ['foo', [Symbol::get('foo')]],
            ['foo bar', [Symbol::get('foo'), Symbol::get('bar')]],
            ['foo/bar', [Symbol::get('foo/bar')]],
            ['foo-bar', [Symbol::get('foo-bar')]],
            ['/', [Symbol::get('/')]],
            [':foo', [Keyword::get('foo')]],
            [':foo :bar', [Keyword::get('foo'), Keyword::get('bar')]],
            [':foo/bar', [Keyword::get('foo/bar')]],
            [':foo-bar', [Keyword::get('foo-bar')]],
            [':/', [Keyword::get('/')]],
            [
                '(defproject com.thortech/data.edn "0.1.0-SNAPSHOT")',
                [
                    new LinkedList([
                        Symbol::get('defproject'),
                        Symbol::get('com.thortech/data.edn'),
                        "0.1.0-SNAPSHOT",
                    ]),
                ],
            ],
            ['1', [1]],
            ['-1', [-1]],
            ['+1', [1]],
            ['0', [0]],
            ['-0', [0]],
            ['+0', [0]],
            ['10', [10]],
            ['20', [20]],
            ['200', [200]],
            ['-200', [-200]],
            ['42', [42]],
            ['1.0', [1.0]],
            ['1.2', [1.2]],
            ['-1.2', [-1.2]],
            ['-0.0', [-0.0]],
            ['-0.25', [-0.25]],
            ['()', [new LinkedList([])]],
            ['(foo)', [new LinkedList([Symbol::get('foo')])]],
            ['(foo bar)', [new LinkedList([Symbol::get('foo'), Symbol::get('bar')])]],
            [
                '(foo bar (baz))',
                [
                    new LinkedList([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        new LinkedList([
                            Symbol::get('baz'),
                        ]),
                    ]),
                ],
            ],
            [
                '(foo bar (baz) qux ((quux)))',
                [
                    new LinkedList([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        new LinkedList([
                            Symbol::get('baz'),
                        ]),
                        Symbol::get('qux'),
                        new LinkedList([
                            new LinkedList([
                                Symbol::get('quux'),
                            ]),
                        ]),
                    ]),
                ],
            ],
            ['[]', [new Vector([])]],
            ['[foo]', [new Vector([Symbol::get('foo')])]],
            ['[foo bar]', [new Vector([Symbol::get('foo'), Symbol::get('bar')])]],
            [
                '[foo bar [baz]]',
                [
                    new Vector([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        new Vector([
                            Symbol::get('baz')
                        ]),
                    ]),
                ],
            ],
            ['{}', [new Map([])]],
            ['{:foo bar}', [new Map([Keyword::get('foo'), Symbol::get('bar')])]],
            ['{:foo (bar)}', [new Map([Keyword::get('foo'), new LinkedList([Symbol::get('bar')])])]],
            ['#{}', [new Set([])]],
            ['#{foo}', [new Set([Symbol::get('foo')])]],
            ['#{foo bar}', [new Set([Symbol::get('foo'), Symbol::get('bar')])]],
            [
                '#{(foo bar)}',
                [
                    new Set([
                        new LinkedList([
                            Symbol::get('foo'),
                            Symbol::get('bar'),
                        ]),
                    ]),
                ],
            ],
            [
                '#{{:foo bar}}',
                [
                    new Set([
                        new Map([
                            Keyword::get('foo'),
                            Symbol::get('bar'),
                        ]),
                    ]),
                ],
            ],
            [
                '#myapp/Person "foo"',
                [
                    new Tagged(new Tag('myapp/Person'), 'foo'),
                ],
            ],
            [
                '{:first "Fred" :last "Mertz"}',
                [
                    new Map([
                        Keyword::get('first'),
                        'Fred',
                        Keyword::get('last'),
                        'Mertz',
                    ]),
                ],
            ],
            [
                '#myapp/Person (:foo :bar)',
                [
                    new Tagged(
                        new Tag('myapp/Person'),
                        new LinkedList([
                            Keyword::get('foo'),
                            Keyword::get('bar'),
                        ])
                    ),
                ],
            ],
            [
                '#myapp/Person {:first "Fred" :last "Mertz"}',
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
            ],
        ];
    }

    /** @test */
    function arrayShouldImplicitlyBecomeList() {
        $data = [[Symbol::get('foo')]];
        $this->assertEquals('(foo)', igorw\edn\encode($data));
    }

    /** @test */
    function assocArrayShouldImplicitlyBecomeKeywordMap() {
        $data = [['foo' => 'bar']];
        $this->assertEquals('{:foo "bar"}', igorw\edn\encode($data));
    }

    /** @test */
    function nonRootAstShouldWrapAsArrayImplicitly() {
        $data = Symbol::get('foo');
        $this->assertEquals('foo', igorw\edn\encode($data));
    }
}
