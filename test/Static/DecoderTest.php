<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use DateTimeImmutable;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Department;
use Klimick\Decode\Test\Static\Fixtures\PartialPerson;
use Klimick\Decode\Test\Static\Fixtures\Person;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;
use Klimick\PsalmTest\StaticType\StaticTypes as t;
use function Klimick\Decode\Decoder\arrList;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\datetime;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\intersection;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\partialObject;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tuple;

final class DecoderTest extends PsalmTest
{
    /**
     * @psalm-suppress UnusedVariable
     *     todo: $self = &$decoder; - false positive
     */
    public function testRecDecoder(): void
    {
        $decoder = rec(function() use (&$decoder) {
            /** @var DecoderInterface<Department> $self */
            $self = &$decoder;

            return object(Department::class)(
                name: string(),
                subDepartments: arrList($self),
            );
        });

        StaticTestCase::describe('Recursive decoder')
            ->haveCode(fn() => $decoder)
            ->seeReturnType(t::generic(
                ofType: DecoderInterface::class,
                withParams: [
                    t::object(Department::class),
                ],
            ));
    }

    public function testObjectDecoder(): void
    {
        StaticTestCase::describe('Object decoder')
            ->haveCode(fn() => object(Person::class)(
                name: string(),
                age: int(),
            ))
            ->seeReturnType(t::generic(
                ofType: ObjectDecoder::class,
                withParams: [
                    t::object(Person::class)
                ],
            ));
    }

    public function testObjectDecoderMissingPropertyIssue(): void
    {
        StaticTestCase::describe('Object decoder: missing property issue')
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
        StaticTestCase::describe('Object decoder: nonexistent property')
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
        StaticTestCase::describe('Object decoder: invalid decoder for property')
            ->haveCode(fn() => object(Person::class)(
                name: string(),
                age: string(),
            ))
            ->seePsalmIssue(
                type: 'InvalidDecoderForPropertyIssue',
                message: 'Invalid decoder for property "age". Expected: #[decoder]<int>. Actual: #[decoder]<string>.',
                args: [
                    'decoder' => DecoderInterface::class,
                ],
            );
    }

    public function testPartialObjectDecoder(): void
    {
        StaticTestCase::describe('Partial object decoder')
            ->haveCode(fn() => partialObject(PartialPerson::class)(
                name: string(),
                age: int(),
            ))
            ->seeReturnType(t::generic(
                ofType: ObjectDecoder::class,
                withParams: [
                    t::object(PartialPerson::class),
                ],
            ));
    }

    public function testPartialObjectAllPropertiesMustBeNullable(): void
    {
        StaticTestCase::describe('Partial object decoder: all properties must be nullable')
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
        StaticTestCase::describe('Tuple decoder')
            ->haveCode(fn() => tuple(
                string(),
                int(),
                bool()->optional()
            ))
            ->seeReturnType(t::generic(
                ofType: DecoderInterface::class,
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
        $expected_decoder_type = t::generic(
            ofType: ShapeDecoder::class,
            withParams: [
                t::shape([
                    'name' => t::string(),
                    'age' => t::int(),
                    'bornAt' => t::object(DateTimeImmutable::class)->optional(),
                ]),
            ],
        );

        StaticTestCase::describe('Shape decoder')
            ->haveCode(fn() => shape(
                name: string(),
                age: int(),
                bornAt: datetime()->optional(),
            ))
            ->seeReturnType($expected_decoder_type);
    }

    public function testIntersectionDecoder(): void
    {
        $expected_decoder_type = t::generic(
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
        );

        StaticTestCase::describe('Intersection decoder')
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                shape(prop3: string(), prop4: string()),
                shape(prop5: string(), prop6: string()),
            ))
            ->seeReturnType($expected_decoder_type);
    }

    public function testIntersectionDecoderIntersectionCollisionIssue(): void
    {
        StaticTestCase::describe('Intersection decoder: properties collision')
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                shape(prop3: string(), prop2: string()),
            ))
            ->seePsalmIssue(
                type: 'IntersectionCollisionIssue',
                message: 'Intersection collision: property "prop2" defined more than once.'
            );
    }
}
