<?php

use igorw\edn\Symbol;
use igorw\edn\Keyword;

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
            [[[]], '()'],
            [[[new Symbol('foo')]], '(foo)'],
            [[[new Symbol('foo'), new Symbol('bar')]], '(foo bar)'],
            [[[new Symbol('foo'), new Symbol('bar'), [new Symbol('baz')]]], '(foo bar (baz))'],
            [
                [[new Symbol('foo'), new Symbol('bar'), [new Symbol('baz')], new Symbol('qux'), [[new Symbol('quux')]]]],
                '(foo bar (baz) qux ((quux)))',
            ],
        ];
    }
}
