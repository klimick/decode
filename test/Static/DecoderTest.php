<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use DateTimeImmutable;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Department;
use Klimick\Decode\Test\Static\Fixtures\PartialPerson;
use Klimick\Decode\Test\Static\Fixtures\Person;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;
use Klimick\PsalmTest\StaticType\StaticTypes as t;
use function Klimick\Decode\Decoder\arr;
use function Klimick\Decode\Decoder\arrKey;
use function Klimick\Decode\Decoder\arrList;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\datetime;
use function Klimick\Decode\Decoder\fallback;
use function Klimick\Decode\Decoder\float;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\intersection;
use function Klimick\Decode\Decoder\literal;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\nonEmptyArr;
use function Klimick\Decode\Decoder\nonEmptyArrList;
use function Klimick\Decode\Decoder\nonEmptyString;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\numeric;
use function Klimick\Decode\Decoder\numericString;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\partialObject;
use function Klimick\Decode\Decoder\positiveInt;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\scalar;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tuple;
use function Klimick\Decode\Decoder\union;

final class DecoderTest extends PsalmTest
{
    public function testStringDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => string())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::string())
            );
    }

    public function testNonEmptyStringDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => nonEmptyString())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::nonEmptyString())
            );
    }

    public function testIntDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => int())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::int())
            );
    }

    public function testPositiveIntDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => positiveInt())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::positiveInt())
            );
    }

    public function testFloatDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => float())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::float())
            );
    }

    public function testBoolDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => bool())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::bool())
            );
    }

    public function testNumericDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => numeric())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::numeric())
            );
    }

    public function testNumericStringDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => numericString())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::numericString())
            );
    }

    public function testArrKeyDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => arrKey())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::arrayKey())
            );
    }

    public function testNullDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => null())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::null())
            );
    }

    public function testMixedDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => mixed())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::mixed())
            );
    }

    public function testScalarDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => scalar())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::scalar())
            );
    }

    public function testFallbackDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => fallback(1))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::literal(1))
            );
    }

    public function testDatetimeDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => datetime())
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::object(DateTimeImmutable::class))
            );
    }

    /**
     * @psalm-suppress UnusedVariable
     *     todo: $self = &$decoder; - false positive
     */
    public function testRecDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                $decoder = rec(function() use (&$decoder) {
                    /** @var AbstractDecoder<Department> $self */
                    $self = &$decoder;

                    return object(Department::class)(
                        name: string(),
                        subDepartments: arrList($self),
                    );
                });

                return $decoder;
            })
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::object(Department::class))
            );
    }

    public function testLiteralDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => literal(1, 2, 3))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::union(
                    t::literal(1),
                    t::literal(2),
                    t::literal(3),
                ))
            );
    }

    public function testUnionDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => union(
                int(),
                string(),
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::union(
                    t::int(),
                    t::string(),
                ))
            );
    }

    public function testObjectDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => object(Person::class)(
                name: string(),
                age: int(),
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::object(Person::class))
            );
    }

    public function testObjectDecoderMissingPropertyIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => object(Person::class)(
                name: string(),
            ))
            ->seePsalmIssue(
                type: 'RequiredObjectPropertyMissingIssue',
                message: 'Required decoders for properties missed: "age"',
            );
    }

    public function testObjectDecoderNonexistentPropertyObjectPropertyIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => object(Person::class)(
                misspelled_name: string(),
                age: int(),
            ))
            ->seePsalmIssue(
                type: 'RequiredObjectPropertyMissingIssue',
                message: 'Required decoders for properties missed: "name"',
            )
            ->seePsalmIssue(
                type: 'NonexistentPropertyObjectPropertyIssue',
                message: 'Property "misspelled_name" does not exist.',
            );
    }

    public function testObjectDecoderInvalidDecoderForPropertyIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => object(Person::class)(
                name: string(),
                age: string(),
            ))
            ->seePsalmIssue(
                type: 'InvalidDecoderForPropertyIssue',
                message: 'Invalid decoder for property "age". Expected: #[decoder]<int>. Actual: #[decoder]<string>.',
                args: [
                    'decoder' => AbstractDecoder::class,
                ],
            );
    }

    public function testPartialObjectDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => partialObject(PartialPerson::class)(
                name: string(),
                age: int(),
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::object(PartialPerson::class))
            );
    }

    public function test(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => partialObject(Person::class)(
                name: string(),
                age: int(),
            ))
            ->seePsalmIssue(
                type: 'NotPartialPropertyIssue',
                message: 'Property "name" must be nullable in source class.'
            )
            ->seePsalmIssue(
                type: 'NotPartialPropertyIssue',
                message: 'Property "age" must be nullable in source class.'
            );
    }

    public function testTupleDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => tuple(
                string(),
                int(),
                bool()->optional()
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::shape([
                    t::string(),
                    t::int(),
                    t::bool()->optional(),
                ]))
            );
    }

    public function testShapeDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => shape(
                name: string(),
                age: int(),
                bornAt: datetime()->optional(),
            ))
            ->seeReturnType(
                t::intersection(
                    t::generic(AbstractDecoder::class, t::shape([
                        'name' => t::string(),
                        'age' => t::int(),
                        'bornAt' => t::object(DateTimeImmutable::class)->optional(),
                    ])),
                    t::generic(ShapeDecoder::class, t::shape([
                        'name' => t::string(),
                        'age' => t::bool(),
                        'bornAt' => t::object(DateTimeImmutable::class)->optional(),
                    ])),
                ),
            );
    }

    public function testIntersectionDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                shape(prop3: string(), prop4: string()),
                shape(prop5: string(), prop6: string()),
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::shape([
                    'prop1' => t::string(),
                    'prop2' => t::string(),
                    'prop3' => t::string(),
                    'prop4' => t::string(),
                    'prop5' => t::string(),
                    'prop6' => t::string(),
                ]))
            );
    }

    public function testIntersectionDecoderIntersectionCollisionIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                shape(prop3: string(), prop2: string()),
            ))
            ->seePsalmIssue(
                type: 'IntersectionCollisionIssue',
                message: 'Intersection collision: property "prop2" defined more than once.'
            );
    }

    public function testArrDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => arr(
                int(),
                string()
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::array(
                    t::int(),
                    t::string(),
                ))
            );
    }

    public function testNonEmptyArrDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => nonEmptyArr(
                int(),
                string()
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::nonEmptyArray(
                    t::int(),
                    t::string(),
                ))
            );
    }

    public function testListDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => arrList(
                int(),
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::list(
                    t::int(),
                ))
            );
    }

    public function testNonEmptyListDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => nonEmptyArrList(
                int()
            ))
            ->seeReturnType(
                t::generic(AbstractDecoder::class, t::nonEmptyList(
                    t::int(),
                ))
            );
    }
}
