# Extensible Data Notation

Parser for the [edn](https://github.com/edn-format/edn) format. In PHP.

The parser internally relies on [Phlexy](https://github.com/nikic/Phlexy) for
lexing.

This library uses the [Ardent](https://github.com/morrisonlevi/Ardent) data
structures to represent lists, vectors, maps and sets. Those are the ones you
will get when parsing edn.

## Usage

### Parsing

To parse edn, just use the `parse` function:

    $edn = file_get_contents('examples/sample.edn');
    $data = igorw\edn\parse($edn);

    print_r($data);

You can also define custom tag handlers and pass them as a second argument to
`parse`:

    use igorw\edn;

    class Person {
        public $firstName;
        public $lastName;

        function __construct($firstName, $lastName) {
            $this->firstName = $firstName;
            $this->lastName = $lastName;
        }
    }

    $edn = '#myapp/Person {:first "Fred" :last "Mertz"}';

    $data = edn\parse($edn, [
        'myapp/Person' => function ($node) {
            return new Person(
                $node[edn\keyword('first')],
                $node[edn\keyword('last')]
            );
        },
    ]);

    // [new Person('Fred', 'Mertz')]

### Encoding

If you want to take an in-memory data structure and encode it as edn, you can
use the `encode` function:

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
    // (foo bar baz :qux 1.0 {:name "igorw"})
