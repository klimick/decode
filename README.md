## Decode

Данная библиотека позволяет проверить, что ненадежные данные типа `mixed` являются типом `T`:

```php
<?php

use Klimick\Decode\Decoder\CastException;
use Klimick\Decode\Decoder as t;

// Describes runtime type for array{name: string, age: int, meta: list<string>}
$personD = t\shape(
    name: t\string(),
    age: t\int(),
    meta: t\arrList(t\string()),
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

// Psalm knows that $person is array{name: string, age: int, meta: list<string>}
// If decode will fail, CastException is thrown.
$person = t\tryCast(
    value: $json,
    to: t\fromJson($personD),
);

print_r($person);
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