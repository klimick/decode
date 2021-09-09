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

### Builtin type atomics

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

##### union(T1(), T2(), T3())
Represents type whose value will be of a single type out of multiple types.

```php
// int | string
$intOrString = union(int(), string());
// float | null
$floatOrNull = union(float(), null());
// int | float | string | null
$intOrFloatOrStringOrNull = union($intOrString, $floatOrNull);
```

##### arr(TK(), TV())
Represent `array` with keys of type `TK()` and values of type `TV()`.

```php
// array<int, string>
$arr = arr(int(), string());
```

##### nonEmptyArr(TK(), TV())
Represent `non-empty-array` with keys of type `TK()` and values of type `TV()`.

```php
// non-empty-array<int, string>
$nonEmptyArr = nonEmptyArr(int(), string());
```

##### arrList(TV())
Represents `list` with values of type `TV()`.

```php
// list<string>
$list = arrList(string());
```

##### nonEmptyArrList(TV())
Represents `non-empty-list` with values of type `TV()`.

```php
// non-empty-list<string>
$list = nonEmptyArrList(string());
```

##### shape(prop1: T(), prop2: T(), propN: T())
Represent `array` with knows keys.

```php
// array{prop1: int, prop2: string, prop3: bool}
$shape = shape(
    prop1: int(),
    prop2: string(),
    prop3: bool(),
);
```

##### partialShape(prop1: T(), prop2: T(), propN: T())
Like `shape` represents `array` with knows keys, but each key is possibly undefined.

```php
// array{prop1?: int, prop2?: string, prop3?: bool}
$shape = partialShape(
    prop1: int(),
    prop2: string(),
    prop3: bool(),
);
```

##### tuple(T(), T(), T())
Represents array that indexed from zero with fixed items count.

```php
// array{int, string, bool}
$tuple = tuple(int(), string(), bool());
```

##### object(SomeClass::class)(prop1: T(), prop2: T(), propN: T())
Allows to create decoder for existed class. For each parameter of the constructor, you must explicitly specify corresponding a decoder. Definition example:
```php
final class SomeClass
{
    public function __construct(
        public int $prop1,
        public string $prop2,
    ) {}
    
    /**
     * @return AbstractDecoder<SomeClass>
     */
    public static function type(): AbstractDecoder
    {
        return object(self::class)(
            prop1: int(),
            prop2: string(),
        );
    }
}
```
To avoid some boilerplate code you may consider `ProductType` or `SumType`.

##### partialObject(SomeClass::class)(prop1: T(), prop2: T(), propN: T())
Like `object` decoder, but each parameter of the constructor must be nullable.

##### rec(fn() => T())
Represents recursive type. Only objects can be recursive. Definition example:
```php
final class SomeClass
{
    /**
     * @param list<SomeClass> $recursive
     */
    public function __construct(
        public int $prop1,
        public string $prop2,
        public array $recursive = [],
    ) {}

    /**
     * @return AbstractDecoder<SomeClass>
     */
    public static function type(): AbstractDecoder
    {
        $self = rec(fn() => self::type());

        return object(self::class)(
            prop1: int(),
            prop2: string(),
            recursive: arrList($self),
        );
    }
}
```

##### fromJson(T())
Combinator for type `T` which will be parsed from json representation.

```php
$shapeFromJson = fromJson(
    shape(
        prop1: string(),
        prop2: string(),
    )
);
```