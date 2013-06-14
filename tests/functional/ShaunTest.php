<?php

use igorw\edn;

class ShaunTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideValidEdnFile */
    function testParseWithValidEdn($ednFile) {
        $edn = file_get_contents($ednFile);

        $data = igorw\edn\parse($edn);
        $encoded = igorw\edn\encode($data);

        $this->assertEquals(trim($edn), trim($encoded));
    }

    function provideValidEdnFile() {
        $dir = __DIR__.'/../../vendor/shaunxcode/edn-tests/valid-edn';
        $files = new \FilesystemIterator($dir);
        return array_map(function ($file) { return [$file]; }, iterator_to_array($files));
    }

    /**
     * @dataProvider provideInvalidEdnFile
     * @expectedException Phlexy\LexingException
     */
    function testParseWithInvalidEdn($ednFile) {
        $edn = file_get_contents($ednFile);

        $data = igorw\edn\parse($edn);

        $this->fail(sprintf('Expected parser to fail on %s, but got: %s', json_encode($edn), print_r($data, true)));
    }

    function provideInvalidEdnFile() {
        $dir = __DIR__.'/../../vendor/shaunxcode/edn-tests/invalid-edn';
        $files = new \FilesystemIterator($dir);
        return array_map(function ($file) { return [$file]; }, iterator_to_array($files));
    }
}
