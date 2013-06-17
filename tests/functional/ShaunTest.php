<?php

use igorw\edn;

class ShaunTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideValidEdnFile */
    function testParseWithValidEdn($ednFile) {
        $edn = file_get_contents($ednFile);

        $expectedFile = $this->shaunDir().'/platforms/php/'.basename($ednFile, '.edn').'.php';
        if (!file_exists($expectedFile)) {
            $this->markTestIncomplete(sprintf('Missing php translation of edn: %s', basename($expectedFile)));
        }
        $expectedData = file_get_contents($expectedFile);
        $expectedCode = 'use igorw\edn; return '.$expectedData.';';
        $expected = $expectedData ? [eval($expectedCode)] : [];

        $data = igorw\edn\parse($edn);

        $this->assertEquals($expected, $data);
    }

    function provideValidEdnFile() {
        $dir = $this->shaunDir().'/valid-edn';
        $files = new \FilesystemIterator($dir);
        return array_map(function ($file) { return [$file]; }, iterator_to_array($files));
    }

    /**
     * @dataProvider provideInvalidEdnFile
     * @expectedException igorw\edn\ParserException
     */
    function testParseWithInvalidEdn($ednFile) {
        $edn = file_get_contents($ednFile);

        $data = igorw\edn\parse($edn);

        $this->fail(sprintf('Expected parser to fail on %s, but got: %s', json_encode($edn), print_r($data, true)));
    }

    function provideInvalidEdnFile() {
        $dir = $this->shaunDir().'/invalid-edn';
        $files = new \FilesystemIterator($dir);
        return array_map(function ($file) { return [$file]; }, iterator_to_array($files));
    }

    private function shaunDir() {
        return __DIR__.'/../../vendor/shaunxcode/edn-tests';
    }
}
