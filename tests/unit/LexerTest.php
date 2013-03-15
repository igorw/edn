<?php

namespace igorw\edn;

class LexerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     * @dataProvider provideInvalidEdn
     * @expectedException Phlexy\LexingException
     */
    function parseShouldRejectInvalidSyntax($edn) {
        tokenize($edn);
    }

    function provideInvalidEdn() {
        return [
            ['##'],
            ['#:foo'],
            [':#foo'],
            [':{'],
            [':}'],
            [':{}'],
            [':^'],
            ['^'],
            ['_:^'],
            ['.9'],
            ['/foo'],
            ['foo/'],
            [':/foo'],
            [':foo/'],
            ['#/foo'],
            ['#foo/'],
            ['foo/bar/baz'],
            ['foo/bar/baz/qux'],
            ['foo/bar/baz/qux/'],
            ['foo/bar/baz/qux/quux'],
            ['//'],
            ['///'],
            ['/foo//'],
            ['///foo'],
        ];
    }
}
