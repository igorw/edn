# Extensible Data Notation

Parser for the [edn](https://github.com/edn-format/edn) format.

## Usage

    $edn = file_get_contents('examples/project.clj');
    $data = igorw\edn\parse($edn);

    print_r($data);

## Todo

* custom tag handlers
* igorw\edn\encode
