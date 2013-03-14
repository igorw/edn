<?php

require __DIR__.'/../vendor/autoload.php';

use igorw\edn\Symbol;
use igorw\edn\Keyword;

$person = new Ardent\HashMap();
$person[Keyword::get('name')] = 'igorw';

$list = new Ardent\LinkedList();
$list->push(Symbol::get('foo'));
$list->push(Symbol::get('bar'));
$list->push(Symbol::get('baz'));
$list->push(Keyword::get('qux'));
$list->push(1.0);
$list->push($person);

$edn = igorw\edn\encode([$list]);
echo "$edn\n";
