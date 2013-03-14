<?php

function dump($data, $depth = 0) {
    if (is_array($data)) {
        print_line('[', $depth);
        foreach ($data as $key => $value) {
            if (!is_int($key)) {
                print_line("$key =>", $depth+1);
            }
            dump($value, $depth+2);
        }
        print_line(']', $depth);
        return;
    }

    if (is_object($data)) {
        if (method_exists($data, '__toString')) {
            $class = get_class($data);
            print_line("[$class] $data", $depth);
            return;
        }

        $class = get_class($data);
        print_line("[$class] {", $depth);
        if ($data instanceof \IteratorAggregate) {
            $iterator = $data->getIterator();
        } elseif ($data instanceof \Iterator) {
            $iterator = $data;
        } else {
            $iterator = new \ArrayIterator((array) $data);
        }
        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            if (!is_int($iterator->key())) {
                dump($iterator->key(), $depth+1);
            }
            dump($iterator->current(), $depth+2);
        }
        print_line('}', $depth);
        return;
    }

    if (is_string($data)) {
        print_line('"'.$data.'"', $depth);
        return;
    }

    print_line($data, $depth);
}

function print_line($data, $depth) {
    echo str_repeat(' ', 2*$depth).$data."\n";
}
