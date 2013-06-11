<?php

require __DIR__.'/../vendor/autoload.php';

use igorw\edn;

$person = new Ardent\HashMap();
$person[edn\keyword('name')] = 'igorw';

$list = new Ardent\LinkedList();
$list->push(edn\symbol('foo'));
$list->push(edn\symbol('bar'));
$list->push(edn\symbol('baz'));
$list->push(edn\keyword('qux'));
$list->push(1.0);
$list->push($person);

$edn = edn\encode([$list]);
echo "$edn\n";
