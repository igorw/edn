<?php

require __DIR__.'/../vendor/autoload.php';

$edn = file_get_contents('examples/project.clj');
$data = igorw\edn\parse($edn);

print_r($data);
