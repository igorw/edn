<?php

namespace igorw\edn;

class LexerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     * @dataProvider provideInvalidEdn
     * @expectedException Phlexy\LexingException
     */
    function parseShouldRejectInvalidSyntax($edn) {
        $data = tokenize($edn);

        $this->fail(sprintf('Expected parser to fail on %s, but got: %s', json_encode($edn), print_r($data, true)));
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
            ['\newline0.1'],
            ['\newline.'],
            ['.\newline'],
        ];
    }
}
