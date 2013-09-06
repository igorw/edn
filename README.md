# Extensible Data Notation

Parser for the [edn](https://github.com/edn-format/edn) format. In PHP.

The parser internally relies on [Phlexy](https://github.com/nikic/Phlexy) for
lexing.

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
    // (foo bar baz :qux 1.0 {:name "igorw"})

## Tests

This library runs against the
[shaunxcode/edn-tests](https://github.com/shaunxcode/edn-tests) suite.
