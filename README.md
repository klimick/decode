## Decode

This library allow you to take untrusted data and check that they can be casted to type `T`.

### Example

```php
<?php

use Klimick\Decode\Decoder\CastException;
use Klimick\Decode\Decoder as t;

// Describes runtime type for array{name: string, age: int, meta: list<string>}
$libraryDefinition = t\shape(
    id: t\int(),
    name: t\string(),
    meta: t\arrList(t\string()),
);

// Untrusted data
$json = '{
    "id": 42,
    "name": "Decode",
    "meta": [
        "runtime type system",
        "psalm integration",
        "with whsv26/functional"
    ]
}';

// If decode will fail, CastException is thrown.
// $person is array{name: string, age: int, meta: list<string>}
$person = t\tryCast(
    value: $json,
    to: t\fromJson($libraryDefinition),
);

// Either data type from whsv26/functional
// Left side contains decoding errors
// Right side holds decoded valid
// $person is Either<Invalid, Valid<array{name: string, age: int, meta: list<string>}>>
$personEither = t\decode(
    value: $json,
    with: t\fromJson($libraryDefinition),
)

// Option data type from whsv26/functional
// $person is Option<array{name: string, age: int, meta: list<string>}>
$personOption = t\cast(
    value: $json,
    to: t\fromJson($libraryDefinition),
);

print_r($person);
```

### Example of class with runtime type safety

```php
use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Decoder as t;

final class Library extends ProductType
{
    protected static function definition(): ShapeDecoder
    {
        return shape(
            id: t\int(),
            name: t\string(),
            meta: t\arrList(t\string()),
        );
    }
}

// Instance of Library created from untrusted data
$fromUntrusted = t\tryCast(
    value: '...any untrusted data...',
    to: Library::type(),
);

// Psalm knows that 'id' property is existed and typed as int
print_r($instance->id);

// Statically type checked
// Args order depends on definition of Library
$createdWithNewExpr = new Library(42, 'Decode', [
    "runtime type system",
    "psalm integration",
    "with whsv26/functional",
]);

// Named args also supported
$createdWithNamedArgs = new Library(
    id: 42,
    name: 'Decode',
    meta: [
        "runtime type system",
        "psalm integration",
        "with whsv26/functional",
    ],
);
```

### Type atomics

| decoder          | php/psalm                 |
| ---------------- | ------------------------- |
| `mixed`          | mixed                     |
| `null`           | null                      |
| `int`            | int                       |
| `positiveInt`    | positive-int              |
| `float`          | float                     |
| `numeric`        | numeric                   |
| `numericString`  | numeric-string            |
| `bool`           | bool                      |
| `string`         | string                    |
| `nonEmptyString` | non-empty-string          |
| `scalar`         | scalar                    |
| `datetime`       | DateTimeImmutable         |
| `arrKey`         | array-key                 |

### Generic types

| decoder          | php/psalm                                                                        |
| ---------------- | -------------------------------------------------------------------------------- |
| `union(int(), string(), null())`                                  | int \| string \| null           |
| `arr(int(), string())`                                            | array<int, string>              |
| `nonEmptyArr(int(), string())`                                    | non-empty-array<int, string>    |
| `arrList(string())`                                               | list<string>                    |
| `nonEmptyArrList(string())`                                       | non-empty-list<string>          |
| `shape(name: string(), age: int)`                                 | array{name: string, age: int}   |
| `partialShape(name: string(), age: int)`                          | array{name?: string, age?: int} |
| `tuple(string(), int())`                                          | array{string, int}              |
| `object(Person::class)(name: string(), age: int())`               | Person                          |
| `partialObject(PartialPerson::class)(name: string(), age: int())` | PartialPerson                   |
| `rec(fn() => anyRecursiveType())`                                 | AnyRecursiveType                |
| `fromJson(shape(name: string(), age: int))`                       | array{name: string, age: int}   |
