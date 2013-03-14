<?php

require __DIR__.'/../vendor/autoload.php';

$edn = file_get_contents('examples/sample.edn');
$data = igorw\edn\parse($edn);

dump($data);

function dump($data, $depth = 0) {
    if (is_array($data)) {
        echo
    }
}

function indent($data, $depth) {
    echo str_repeat(' ', 4*$depth).$data;
}
