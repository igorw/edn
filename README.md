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

    class Person {
        public $firstName;
        public $lastName;

        function __construct($firstName, $lastName) {
            $this->firstName = $firstName;
            $this->lastName = $lastName;
        }
    }

    $edn = '#myapp/Person {:first "Fred" :last "Mertz"}';

    $data = igorw\edn\parse($edn, [
        'myapp/Person' => function ($node) {
            return new Person(
                $node[Keyword::get('first')],
                $node[Keyword::get('last')]
            );
        },
    ]);

### Encoding

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
