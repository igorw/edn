<?php

require __DIR__.'/../vendor/autoload.php';

use igorw\edn;

$person = new edn\Map();
$person[edn\keyword('name')] = 'igorw';

$list = new edn\LinkedList([
    edn\symbol('foo'),
    edn\symbol('bar'),
    edn\symbol('baz'),
    edn\keyword('qux'),
    1.0,
    $person,
]);

$edn = edn\encode([$list]);
echo "$edn\n";
