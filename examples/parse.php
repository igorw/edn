<?php

require __DIR__.'/../vendor/autoload.php';

$edn = file_get_contents('examples/sample.edn');
$data = igorw\edn\parse($edn);

print_r($data);
