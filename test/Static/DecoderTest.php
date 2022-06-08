<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use DateTimeImmutable;
use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use Fp\PsalmToolkit\StaticType\StaticTypes as t;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Department;
use Klimick\Decode\Test\Static\Fixtures\Person;
use function Klimick\Decode\Decoder\datetime;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\intersection;
use function Klimick\Decode\Decoder\listOf;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tagged;

final class DecoderTest extends PsalmTest
{
    public function testTaggedUnion(): void
    {
        StaticTestCase::describe('All args must be named')
            ->haveCode(fn() => tagged(with: 'type')(
                shape(foo: string(), bar: int()),
                type2: shape(id: int(), num: int()),
            ))
            ->seePsalmIssue(
                type: 'NotNamedArgForTaggedUnion',
                message: 'All args for tagged union must be named',
            );

        StaticTestCase::describe('At leas two decoders')
            ->haveCode(fn() => tagged(with: 'type')(
                type1: shape(foo: string(), bar: int()),
            ))
            ->seePsalmIssue(
                type: 'TooFewArgsForTaggedUnion',
                message: 'Too few args passed for tagged',
            );
    }

    /**
     * @psalm-suppress UnusedVariable todo: &$decoder false positive
     */
    public function testRecDecoder(): void
    {
        $decoder = rec(function() use (&$decoder) {
            /** @var DecoderInterface<Department> $self */
            $self = &$decoder;

            return object(Department::class)(
                name: string(),
                subDepartments: listOf($self),
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
                ofType: DecoderInterface::class,
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
                type: 'RequiredObjectPropertyMissing',
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
                type: 'RequiredObjectPropertyMissing',
                message: 'Required decoders for properties missed: "name"',
            )
            ->seePsalmIssue(
                type: 'NonexistentPropertyObjectProperty',
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
                type: 'InvalidDecoderForProperty',
                message: 'Invalid decoder for property "age". Expected: #[decoder]<int>. Actual: #[decoder]<string>.',
                args: [
                    'decoder' => DecoderInterface::class,
                ],
            );
    }

    public function testShapeDecoder(): void
    {
        StaticTestCase::describe('Shape decoder')
            ->haveCode(fn() => shape(
                name: string(),
                age: int(),
                bornAt: datetime()->orUndefined(),
            ))
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape([
                        'name' => t::string(),
                        'age' => t::int(),
                        'bornAt' => t::object(DateTimeImmutable::class)->optional(),
                    ]),
                ])
            );
    }

    public function testIntersectionDecoder(): void
    {
        StaticTestCase::describe('Intersection decoder')
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                shape(prop3: string(), prop4: string()),
                shape(prop5: string(), prop6: string()),
            ))
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape([
                        'prop1' => t::string(),
                        'prop2' => t::string(),
                        'prop3' => t::string(),
                        'prop4' => t::string(),
                        'prop5' => t::string(),
                        'prop6' => t::string(),
                    ])
                ])
            );
    }

    public function testNestedIntersectionDecoder(): void
    {
        StaticTestCase::describe('Intersection decoder')
            ->haveCode(fn() => intersection(
                shape(prop1: string(), prop2: string()),
                intersection(
                    shape(prop3: string(), prop4: string()),
                    shape(prop5: string(), prop6: string()),
                ),
            ))
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape([
                        'prop1' => t::string(),
                        'prop2' => t::string(),
                        'prop3' => t::string(),
                        'prop4' => t::string(),
                        'prop5' => t::string(),
                        'prop6' => t::string(),
                    ])
                ]),
            );
    }

    public function testPickProps(): void
    {
        StaticTestCase::describe('Pick prop')
            ->haveCode(function() {
                $shape = shape(
                    id: string(),
                    name: string(),
                    meta: string(),
                );

                return $shape->pick(['id']);
            })
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape(['id' => t::string()])
                ])
            );

        StaticTestCase::describe('Pick multiple props')
            ->haveCode(function() {
                $shape = shape(
                    id: string(),
                    name: string(),
                    meta: string(),
                );

                return $shape->pick(['id', 'name']);
            })
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape(['id' => t::string(), 'name' => t::string()])
                ])
            );

        StaticTestCase::describe('Pick unknown prop')
            ->haveCode(function() {
                return shape(id: string())->pick(['unknown']);
            })
            ->seePsalmIssue(
                type: 'UndefinedShapeProperty',
                message: 'Property #[property] is not defined on shape #[shape]',
                args: [
                    'property' => 'unknown',
                    'shape' => 'array{id: string}',
                ]
            );

        StaticTestCase::describe('Pick multiple unknown props')
            ->haveCode(function() {
                return shape(id: string())->pick(['unknown1', 'unknown2']);
            })
            ->seePsalmIssue(
                type: 'UndefinedShapeProperty',
                message: 'Properties #[properties] are not defined on shape #[shape]',
                args: [
                    'properties' => 'unknown1, unknown2',
                    'shape' => 'array{id: string}',
                ]
            );
    }

    public function testOmitProps(): void
    {
        StaticTestCase::describe('Omit prop')
            ->haveCode(function() {
                $shape = shape(
                    id: string(),
                    name: string(),
                    meta: string(),
                );

                return $shape->omit(['id']);
            })
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape(['name' => t::string(), 'meta' => t::string()])
                ])
            );

        StaticTestCase::describe('Omit multiple props')
            ->haveCode(function() {
                $shape = shape(
                    id: string(),
                    name: string(),
                    meta: string(),
                );

                return $shape->omit(['id', 'name']);
            })
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape(['meta' => t::string()])
                ])
            );

        StaticTestCase::describe('Omit unknown prop')
            ->haveCode(function() {
                return shape(id: string())->omit(['unknown']);
            })
            ->seePsalmIssue(
                type: 'UndefinedShapeProperty',
                message: 'Property #[property] is not defined on shape #[shape]',
                args: [
                    'property' => 'unknown',
                    'shape' => 'array{id: string}',
                ]
            );

        StaticTestCase::describe('Pick multiple unknown props')
            ->haveCode(function() {
                return shape(id: string())->omit(['unknown1', 'unknown2']);
            })
            ->seePsalmIssue(
                type: 'UndefinedShapeProperty',
                message: 'Properties #[properties] are not defined on shape #[shape]',
                args: [
                    'properties' => 'unknown1, unknown2',
                    'shape' => 'array{id: string}',
                ]
            );
    }
}
