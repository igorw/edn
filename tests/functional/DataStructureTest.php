<?php

use igorw\edn\Symbol;
use igorw\edn\Keyword;
use igorw\edn;

class DataStructureTest extends \PHPUnit_Framework_TestCase {
    function testSymbol() {
        $symbol = edn\symbol('foo');
        $this->assertSame(Symbol::get('foo'), $symbol);
    }

    function testKeyword() {
        $keyword = edn\keyword('foo');
        $this->assertSame(Keyword::get('foo'), $keyword);
    }
}
