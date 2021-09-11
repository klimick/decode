## Decode

This library allow you to take untrusted data and check that they can be casted to type `T`.

- [Usage example](#example)
- [Built in atomics](#builtin-type-atomics)
- [Generic types](#generic-types)
- [Class with runtime type safety (Product type)](#product-type)
- [Closed union type with runtime type safety (Sum type)](#sum-type)

## Example

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
Allows to create decoder for existed class. For each parameter of the constructor, you must explicitly specify a corresponding decoder.
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
To avoid some boilerplate code you may consider to use [product type](#product-type) or [sum type](#sum-type).

##### partialObject(SomeClass::class)(prop1: T(), prop2: T(), propN: T())
Like `object` decoder, but each parameter of the constructor must be nullable.

##### rec(fn() => T())
Represents recursive type. Only objects can be recursive.

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

### High order decoders

##### from('$.some_prop')
Helper method `from` is defined for each decoder.
It allows you to specify path for result property or rename one.

```php
$personD = shape(
    name: string()->from('$.person'),
    street: string()->from('$.address.street'),
);

$untrustedData = [
    'person' => 'foo',
    'address' => [
        'street' => 'bar',
    ],
];

// Inferred type: array{name: string, street: string}
$personShape = tryCast($untrustedData, $personD);

/* Decoded data looks different rather than source: [
    'name' => 'foo',
    'street' => 'bar',
] */
print_r($personShape);
```

`$` sign means root of object. You can use just `$` when you want to change decoded structure nesting:

```php
$messengerD = shape(
    kind: string()->from('$.messenger_type'),
    contact: string()->from('$.messenger_contact'),
);

$personD = shape(
    name: string()->from('$.person'),
    street: string()->from('$.address.street'),
    messenger: $messengerD->from('$'), // means "use the same data from this decoder"
);

$untrustedData = [
    'person' => 'foo',
    'address' => [
        'street' => 'bar',
    ],
    'messenger_type' => 'telegram',
    'messenger_contact' => '@Klimick',
];

// inferred type: array{name: string, street: string, messenger: array{kind: string, messenger: string}}
$personShape = tryCast($untrustedData, $personD);

/* Decoded data looks different rather than source: [
    'name' => 'foo',
    'street' => 'bar',
    'messenger' => [
        'kind' => 'telegram',
        'contact' => '@Klimick',
    ]
] */
print_r($personShape);
```

### Product type

Class with runtime type safety.
Properties will be inferred from definition methods.

```php
use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Decoder as t;

/**
 * @psalm-immutable
 */
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
```

This kind of class is not intended for creating from trusted data.
But you can do it with the new expression.

```php
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

### Sum type

```php
use Klimick\Decode\Decoder\SumType;
use Klimick\Decode\Decoder\SumCases;
use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Decoder as t;

/**
 * @psalm-immutable
 */
final class Messenger extends SumType
{
    protected static function definition(): SumCases
    {
        return t\cases(
            telegram: Telegram::type(),
            whatsapp: Whatsapp::type(),
        );
    }
}

/**
 * @psalm-immutable
 * Case of closed Messenger union
 */
final class Telegram extends ProductType
{
    protected static function definition(): ShapeDecoder
    {
        return t\shape(
            telegramId: t\string(),
            // ...rest
        );
    }
}

/**
 * @psalm-immutable
 * Case of closed Messenger union
 */
final class Whatsapp extends ProductType
{
    protected static function definition(): ShapeDecoder
    {
        return t\shape(
            phone: t\string(),
            // ...rest
        );
    }
}

// Instance of Messenger created from untrusted data
$fromUntrusted = t\tryCast(
    value: '...any untrusted data...',
    to: Messenger::type(),
);

// The match method is only one possible way to work with SumType instances
// Name of named arg depends on the definition method of the Messenger
print_r($fromUntrusted->match(
    telegram: fn(Telegram $m) => print_r($m->telegramId),
    whatsapp: fn(Whatsapp $m) => print_r($m->phone),
));
```

Like the [SumType](#product-type) this kind of class is not intended for creating from trusted data.
But you can do it with the new expression.

```php
// A case must be wrapped within Messenger instance
$createdWithNewExpr = new Messenger(
    case: new Telegram('@Klimick'),
);
```