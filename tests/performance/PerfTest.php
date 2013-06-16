<?php

use igorw\edn;

class PerfTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider providePerformanceEdnFile */
    function testPerformance($ednFile) {
        $edn = file_get_contents($ednFile);

        $data = igorw\edn\parse($edn);
    }

    function providePerformanceEdnFile() {
        $dir = __DIR__.'/../../vendor/shaunxcode/edn-tests/performance';
        $files = new \FilesystemIterator($dir);
        return array_map(function ($file) { return [$file]; }, iterator_to_array($files));
    }
}
