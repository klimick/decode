# Decode

### Bring untyped data into the typed in the type safe way.

This library allow you to take untrusted data and check that they can be casted to type `T`.
This is done by means of composable decoders of primitives, literals, arrays, object, unions, intersections and more.

## Try-catch example

`tryCast` cast value of type `mixed` to known type `T`.
Otherwise, `Klimick\Decode\Decoder\CastException` will be thrown.

```php
<?php

use Klimick\Decode\Decoder\CastException;
use function Klimick\Decode\Decoder\arrList;
use function Klimick\Decode\Decoder\fromJson;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tryCast;

// Describes runtime type for array{name: string, age: int, meta: list<string>}
$personD = shape(
    name: string(),
    age: int(),
    meta: arrList(string()),
);

// Untrusted data
$json = '{
    "name": "foobar",
    "age": 42,
    "meta": [
        "runtime type system",
        "with psalm integration",
        "build with whsv26/functional"
    ]
}';

try {
    // Psalm knows that $person is array{name: string, age: int, meta: list<string>}
    $person = tryCast(
        value: $json,
        to: fromJson($personD),
    );

    print_r($person);
} catch (CastException $e) {
    // Klimick\Decode\Report\ErrorReport with failure details
    print_r($e->getErrorReport());

    throw $e;
}

```

## TODO
