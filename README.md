# Extensible Data Notation

Parser for the [edn](https://github.com/edn-format/edn) format.

## Usage

To parse edn, just use the `parse` function:

    $edn = file_get_contents('examples/sample.edn');
    $data = igorw\edn\parse($edn);

    print_r($data);

If you want to take an in-memory data structure and encode it as edn, you can
use the `encode` function:

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
    // (foo bar baz :qux 1.0 {:name "igorw"})
