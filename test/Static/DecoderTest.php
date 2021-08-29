<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use DateTimeImmutable;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Internal\UnionDecoder;
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
use function Klimick\Decode\Decoder\constant;
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
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::string(),
                ],
            ));
    }

    public function testNonEmptyStringDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => nonEmptyString())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::nonEmptyString(),
                ],
            ));
    }

    public function testIntDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => int())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::int(),
                ],
            ));
    }

    public function testPositiveIntDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => positiveInt())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::positiveInt(),
                ],
            ));
    }

    public function testFloatDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => float())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::float(),
                ],
            ));
    }

    public function testBoolDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => bool())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::bool(),
                ],
            ));
    }

    public function testNumericDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => numeric())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::numeric(),
                ],
            ));
    }

    public function testNumericStringDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => numericString())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::numericString(),
                ],
            ));
    }

    public function testArrKeyDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => arrKey())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::arrayKey(),
                ],
            ));
    }

    public function testNullDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => null())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::null(),
                ],
            ));
    }

    public function testMixedDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => mixed())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::mixed(),
                ],
            ));
    }

    public function testScalarDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => scalar())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::scalar(),
                ],
            ));
    }

    public function testConstantDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => constant(1))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::literal(1),
                ],
            ));
    }

    public function testDatetimeDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => datetime())
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::object(DateTimeImmutable::class),
                ],
            ));
    }

    /**
     * @psalm-suppress UnusedVariable
     *     todo: $self = &$decoder; - false positive
     */
    public function testRecDecoder(): void
    {
        $decoder = rec(function() use (&$decoder) {
            /** @var AbstractDecoder<Department> $self */
            $self = &$decoder;

            return object(Department::class)(
                name: string(),
                subDepartments: arrList($self),
            );
        });

        StaticTestCase::describe()
            ->haveCode(fn() => $decoder)
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::object(Department::class),
                ],
            ));
    }

    public function testLiteralDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => literal(1, 2, 3))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::union([
                        t::literal(1),
                        t::literal(2),
                        t::literal(3),
                    ]),
                ]
            ));
    }

    public function testUnionDecoder(): void
    {
        $expected_decoder_type = t::intersection([
            t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::union([
                        t::int(),
                        t::string(),
                    ]),
                ],
            ),
            t::generic(
                ofType: UnionDecoder::class,
                withParams: [
                    t::union([
                        t::int(),
                        t::string(),
                    ])
                ]
            )
        ]);

        StaticTestCase::describe()
            ->haveCode(fn() => union(
                int(),
                string(),
            ))
            ->seeReturnType($expected_decoder_type);
    }

    public function testObjectDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => object(Person::class)(
                name: string(),
                age: int(),
            ))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::object(Person::class)
                ],
            ));
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
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::object(PartialPerson::class),
                ],
            ));
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
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::shape([
                        t::string(),
                        t::int(),
                        t::bool()->optional(),
                    ]),
                ],
            ));
    }

    public function testShapeDecoder(): void
    {
        $expected_decoder_type = t::intersection([
            t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::shape([
                        'name' => t::string(),
                        'age' => t::int(),
                        'bornAt' => t::object(DateTimeImmutable::class)->optional(),
                    ]),
                ],
            ),
            t::generic(
                ofType: ShapeDecoder::class,
                withParams: [
                    t::shape([
                        'name' => t::string(),
                        'age' => t::int(),
                        'bornAt' => t::object(DateTimeImmutable::class)->optional(),
                    ]),
                ],
            ),
        ]);

        StaticTestCase::describe()
            ->haveCode(fn() => shape(
                name: string(),
                age: int(),
                bornAt: datetime()->optional(),
            ))
            ->seeReturnType($expected_decoder_type);
    }

    public function testIntersectionDecoder(): void
    {
        $expected_decoder_type = t::intersection([
            t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::shape([
                        'prop1' => t::string(),
                        'prop2' => t::string(),
                        'prop3' => t::string(),
                        'prop4' => t::string(),
                        'prop5' => t::string(),
                        'prop6' => t::string(),
                    ]),
                ],
            ),
            t::generic(
                ofType: ShapeDecoder::class,
                withParams: [
                    t::shape([
                        'prop1' => t::string(),
                        'prop2' => t::string(),
                        'prop3' => t::string(),
                        'prop4' => t::string(),
                        'prop5' => t::string(),
                        'prop6' => t::string(),
                    ]),
                ],
            ),
        ]);

        StaticTestCase::describe()
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                shape(prop3: string(), prop4: string()),
                shape(prop5: string(), prop6: string()),
            ))
            ->seeReturnType($expected_decoder_type);
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
            ->haveCode(fn() => arr(int(), string()))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::array(t::int(), t::string()),
                ],
            ));
    }

    public function testNonEmptyArrDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => nonEmptyArr(
                int(),
                string()
            ))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::nonEmptyArray(t::int(), t::string()),
                ],
            ));
    }

    public function testListDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => arrList(int()))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::list(t::int()),
                ],
            ));
    }

    public function testNonEmptyListDecoder(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => nonEmptyArrList(int()))
            ->seeReturnType(t::generic(
                ofType: AbstractDecoder::class,
                withParams: [
                    t::nonEmptyList(t::int()),
                ],
            ));
    }
}
