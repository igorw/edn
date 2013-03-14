<?php

use igorw\edn\Symbol;
use igorw\edn\Keyword;
use igorw\edn\Map;
use igorw\edn\Set;
use igorw\edn\Tag;
use igorw\edn\Tagged;
use igorw\edn;

class EncoderTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideAst */
    public function testParse($expected, $ast) {
        $edn = igorw\edn\encode($ast);
        $this->assertEquals($expected, $edn);
    }

    public function provideAst() {
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
            // [
            //     '(defproject com.thortech/data.edn "0.1.0-SNAPSHOT")',
            //     [
            //         edn\create_list([
            //             Symbol::get('defproject'),
            //             Symbol::get('com.thortech/data.edn'),
            //             "0.1.0-SNAPSHOT",
            //         ]),
            //     ],
            // ],
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
            ['()', [edn\create_list([])]],
            ['(foo)', [edn\create_list([Symbol::get('foo')])]],
            ['(foo bar)', [edn\create_list([Symbol::get('foo'), Symbol::get('bar')])]],
            [
                '(foo bar (baz))',
                [
                    edn\create_list([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        edn\create_list([
                            Symbol::get('baz'),
                        ]),
                    ]),
                ],
            ],
            [
                '(foo bar (baz) qux ((quux)))',
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
            ],
            ['[]', [edn\create_vector([])]],
            ['[foo]', [edn\create_vector([Symbol::get('foo')])]],
            ['[foo bar]', [edn\create_vector([Symbol::get('foo'), Symbol::get('bar')])]],
            [
                '[foo bar [baz]]',
                [
                    edn\create_vector([
                        Symbol::get('foo'),
                        Symbol::get('bar'),
                        edn\create_vector([
                            Symbol::get('baz')
                        ]),
                    ]),
                ],
            ],
            ['{}', [edn\create_map([])]],
            ['{:foo bar}', [edn\create_map([Keyword::get('foo'), Symbol::get('bar')])]],
            ['{:foo (bar)}', [edn\create_map([Keyword::get('foo'), edn\create_list([Symbol::get('bar')])])]],
            ['#{}', [edn\create_set([])]],
            ['#{foo}', [edn\create_set([Symbol::get('foo')])]],
            ['#{foo bar}', [edn\create_set([Symbol::get('foo'), Symbol::get('bar')])]],
            [
                '#{(foo bar)}',
                [
                    edn\create_set([
                        edn\create_list([
                            Symbol::get('foo'),
                            Symbol::get('bar'),
                        ]),
                    ]),
                ],
            ],
            [
                '#{{:foo bar}}',
                [
                    edn\create_set([
                        edn\create_map([
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
                    edn\create_map([
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
                        edn\create_list([
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
                        edn\create_map([
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
}
