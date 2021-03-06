## Decode

![psalm level](https://shepherd.dev/github/klimick/decode/level.svg)
![psalm type coverage](https://shepherd.dev/github/klimick/decode/coverage.svg)
[![phpunit coverage](https://coveralls.io/repos/github/klimick/decode/badge.svg)](https://coveralls.io/github/klimick/decode)

This library allow you to take untrusted data and check that it can be represented as type `T`.

- [Usage example](#usage-example)
- [Built in atomics](#builtin-type-atomics)
- [Generic types](#generic-types)
- [Higher order helpers](#higher-order-helpers)
- [Constraints](#constraints)

## Usage example

```php
<?php

use Klimick\Decode\Decoder as t;

// Describes runtime type for array{name: string, age: int, meta: list<string>}
$libraryDefinition = t\shape(
    id: t\int(),
    name: t\string(),
    meta: t\listOf(t\string()),
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
```

### Builtin type atomics

##### mixed()
Represents value of any possible type.

##### null()
Represents type for null value.
Suitable for nullable types.
```php
$nullOrInt = union(null(), int())
```

##### int()
Represents integer number.

##### positiveInt()
Represents positive integer number.

##### float()
Represents number with floating point.

##### numeric()
Represents either integer or float numbers.

##### numericString()
Like `numeric()` but represents also string numbers.

#### bool()
Represents boolean value.

#### string()
Represents string value.

#### nonEmptyString()
Represents string that cannot be empty.

#### scalar()
Any scalar value.

#### arrKey()
Represents array key (int | string)

#### datetime()
Represents decoder that can create `DateTimeImmutable` from string.
It uses the constructor of `DateTimeImmutable` by default.

You can specify a format, and then the decoder will be use `DateTimeImmutable::createFromFormat`:
```php
$datetime = datetime(fromFormat: 'Y-m-d H:i:s');
```

It uses UTC timezone by default.
You can pass different time zone during decoder instantiation:
```php
$datetime = datetime(timezone: 'Moscow/Europe');
```

### Generic types

##### union(T1, T2, T3)
Represents type whose value will be of a single type out of multiple types.

```php
// int | string
$intOrString = union(int(), string());
// float | null
$floatOrNull = union(float(), null());
// int | float | string | null
$intOrFloatOrStringOrNull = union($intOrString, $floatOrNull);
```

##### arrayOf(TK, TV)
Represents `array` with keys of type `TK` and values of type `TV`.

```php
// array<int, string>
$arr = arrayOf(int(), string());
```

##### nonEmptyArrayOf(TK, TV)
Represents `non-empty-array` with keys of type `TK` and values of type `TV`.

```php
// non-empty-array<int, string>
$nonEmptyArr = nonEmptyArrayOf(int(), string());
```

##### listOf(TV)
Represents `list` with values of type `TV`.

```php
// list<string>
$list = listOf(string());
```

##### nonEmptyListOf(TV)
Represents `non-empty-list` with values of type `TV`.

```php
// non-empty-list<string>
$list = nonEmptyListOf(string());
```

##### shape(prop1: T, prop2: T, propN: T)
Represents `array` with knows keys.

```php
// array{prop1: int, prop2: string, prop3: bool}
$shape = shape(
    prop1: int(),
    prop2: string(),
    prop3: bool(),
);
```

##### partialShape(prop1: T, prop2: T, propN: T)
Like `shape` represents `array` with knows keys, but each key is possibly undefined.

```php
// array{prop1?: int, prop2?: string, prop3?: bool}
$shape = partialShape(
    prop1: int(),
    prop2: string(),
    prop3: bool(),
);
```

##### intersection(T1, T2, T3)
Decoder that allows to combine multiple `shape` or `partialShape` into the one.

```php
// array{prop1: string, prop2: string, prop3?: string, prop4?: string}
$intersection = intersection(
    shape(
        prop1: string(),
        prop2: string(),
    ),
    partialShape(
        prop3: string(),
        prop4: string(),
    ),
);
```

##### tuple(T1, T2, T3)
Represents array that indexed from zero with fixed items count.

```php
// array{int, string, bool}
$tuple = tuple(int(), string(), bool());
```

##### object(SomeClass::class)(prop1: T1, prop2: T2, propN: TN)
Allows to create decoder for existed class. For each parameter of the constructor, you must explicitly specify a corresponding decoder.
```php
final class SomeClass
{
    public function __construct(
        public int $prop1,
        public string $prop2,
    ) {}
    
    /**
     * @return DecoderInterface<SomeClass>
     */
    public static function type(): DecoderInterface
    {
        return object(self::class)(
            prop1: int(),
            prop2: string(),
        );
    }
}
```

##### partialObject(SomeClass::class)(prop1: T1, prop2: T2, propN: T3)
Like `object` decoder, but each parameter of the constructor must be nullable.

##### rec(fn() => T)
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
    ) { }

    /**
     * @return DecoderInterface<SomeClass>
     */
    public static function type(): DecoderInterface
    {
        $self = rec(fn() => self::type());

        return object(self::class)(
            prop1: int(),
            prop2: string(),
            recursive: listOf($self),
        );
    }
}
```

##### fromJson(T)
Combinator for decoder of type `T` which will be parsed from json representation.

```php
$shapeFromJson = fromJson(
    shape(
        prop1: string(),
        prop2: string(),
    )
);
```

### Higher order helpers

##### optional
Allows you to mark property as possibly undefined.

```php
$personD = shape(
    name: string(),
    additional: listOf(string())->optional(),
);

// inferred type: array{name: string, additional?: list<string>}
$firstShape = tryCast(['name' => 'foo'], $personD);

// No additional field
// ['name' => 'foo']
print_r($firstShape);

// inferred type: array{name: string, additional?: list<string>}
$secondShape = tryCast(['name' => 'foo', 'additional' => ['bar']], $personD);

// ['name' => 'foo', 'additional' => ['bar']]
print_r($secondShape);
```

##### default
Allows you to define a fallback value if an untrusted source does not present one.

```php
$personD = shape(
    name: string(),
    isEmployed: bool()->default(false),
);

// inferred type: array{name: string, isEmployed: bool}
$firstShape = tryCast(['name' => 'foo'], $personD);

// With default ['isEmployed' => false]
// ['name' => 'foo', 'isEmployed' => false]
print_r($firstShape);

// inferred type: array{name: string, isEmployed: bool}
$secondShape = tryCast(['name' => 'foo', 'isEmployed' => true], $personD);

// ['name' => 'foo', 'isEmployed' => true]
print_r($secondShape);
```

##### constrained
All decoders additionally can be constrained.

```php
$personD = shape(
    name: string()->constrained(
        minSize(is: 1),
        maxSize(is: 255),
    ),
    street: string()->constrained(
        minSize(is: 1),
        maxSize(is: 255),
    ),
);
```

[List of builtin constraints](#constraints)

##### from
Helper method `from` is defined for each decoder.
It allows you to specify a path for a result property or rename one.

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

The `$` sign means root of object. You can use just `$` when you want to change decoded structure nesting:

```php
$messengerD = shape(
    kind: string()->from('$.messenger_type'),
    contact: string()->from('$.messenger_contact'),
);

$personD = shape(
    name: string()->from('$.person'),
    street: string()->from('$.address.street'),
    messenger: $messengerD->from('$'), // means "use the same data for this decoder"
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

### Constraints

Constraints can be attached to decoder with the [constrained](#constrained) higher order helper.

##### equal (all types)
Checks that a numeric value is equal to the given one.

```php
$fooString = string()
    ->constrained(equal('foo'));
```

##### greater (int, float, numeric)
Checks that a numeric value is greater than the given one.

```php
$greaterThan10 = int()
    ->constrained(greater(10));
```

##### greaterOrEqual (int, float, numeric)
Checks that a numeric value is greater or equal to the given one.

```php
$greaterOrEqualTo10 = int()
    ->constrained(greaterOrEqual(10));
```

##### less (int, float, numeric)
Checks that a numeric value is less than the given one.

```php
$lessThan10 = int()
    ->constrained(less(10));
```

##### lessOrEqual (int, float, numeric)
Checks that a numeric value is less or equal to the given one.

```php
$lessOrEqualTo10 = int()
    ->constrained(lessOrEqual(10));
```

##### inRange (int, float, numeric)
Checks that a numeric value is in the given range

```php
$from10to20 = int()
    ->constrained(inRange(10, 20));
```

##### minLength (string, non-empty-string)
Checks that a string value size is not less than given one.

```php
$min10char = string()
    ->constrained(minLength(10));
```

##### maxLength (string, non-empty-string)
Checks that a string value size is not greater than given one.

```php
$max10char = string()
    ->constrained(maxLength(10));
```

##### startsWith (string, non-empty-string)
Checks that a string value starts with the given value.

```php
$startsWithFoo = string()
    ->constrained(startsWith('foo'));
```

##### endsWith (string, non-empty-string)
Checks that a string value ends with the given value.

```php
$endsWithFoo = string()
    ->constrained(endsWith('foo'));
```

##### uuid (string, non-empty-string)
Checks that a string value is a valid UUID.

```php
$uuidString = string()
    ->constrained(uuid());
```

##### trimmed (string, non-empty-string)
Checks that a string value has no leading or trailing whitespace.

```php
$noLeadingOrTrailingSpaces = string()
    ->constrained(trimmed());
```

##### matchesRegex (string, non-empty-string)
Checks that a string value matches the given regular expression.

```php
$stringWithNumbers = string()
    ->constrained(matchesRegex('/^[0-9]{1,3}$/'));
```

##### forall (array<array-key, T>)
Checks that the given constraint holds for all elements of an array value.

```php
$allNumbersGreaterThan10 = forall(greater(than: 10));

$numbersGreaterThan10 = listOf(int())
    ->constrained($allNumbersGreaterThan10);
```

##### exists (array<array-key, T>)
Checks that the given constraint holds for some elements of an array value.

```php
$hasNumbersGreaterThan10 = exists(greater(than: 10));

$withNumberGreaterThan10 = listOf(int())
    ->constrained($hasNumbersGreaterThan10);
```

##### inCollection (array<array-key, T>)
Checks that an array value contains a value equal to the given one.

```php
$listWith10 = listOf(int())
    ->constrained(inCollection(10));
```

##### maxSize (array<array-key, T>)
Checks that an array value size is not greater than the given one.

```php
$max10numbers = listOf(int())
    ->constrained(maxSize(is: 10));
````

##### minSize (array<array-key, T>)
Checks that an array value size is not less than the given one.

```php
$atLeast10numbers = listOf(int())
    ->constrained(minSize(is: 10));
````

##### allOf (any type)
Conjunction of all constraints.

```php
$from100to200 = allOf(
    greaterOrEqual(to: 100),
    lessOrEqual(to: 200),
);

$numbersFrom100to200 = listOf(int())
    ->constrained($from100to200);
```

##### anyOf (any type)
Disjunction of all constraints.

```php
$from100to200 = allOf(
    greaterOrEqual(to: 100),
    lessOrEqual(to: 200),
);

$from300to400 = allOf(
    greaterOrEqual(to: 300),
    lessOrEqual(to: 400),
);

$numbersFrom100to200orFrom300to400 = listOf(int())
    ->constrained(anyOf($from100to200, $from300to400));
```
