<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/pretty_print.php';

$edn = file_get_contents('examples/sample.edn');
$data = igorw\edn\parse($edn);

dump($data);
