<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Fp\PsalmToolkit\StaticTest\NoCode;
use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use Fp\PsalmToolkit\StaticType\StaticTypes as t;
use Klimick\Decode\Decoder\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\IntersectionWithOther;
use Klimick\Decode\Test\Static\Fixtures\IntersectionWithOtherOmit;
use Klimick\Decode\Test\Static\Fixtures\IntersectionWithOtherPick;
use Klimick\Decode\Test\Static\Fixtures\NewShapeByOther;
use Klimick\Decode\Test\Static\Fixtures\Project;
use Klimick\Decode\Test\Static\Fixtures\RecByFqn;
use Klimick\Decode\Test\Static\Fixtures\RecBySelf;
use Klimick\Decode\Test\Static\Fixtures\ShapeWithOther;
use Klimick\Decode\Test\Static\Fixtures\ShapeWithValueObject;
use Klimick\Decode\Test\Static\Fixtures\SomeValueObjectWithSelfKeyword;
use Klimick\Decode\Test\Static\Fixtures\SomeValueObjectWithTypeHint;
use Klimick\Decode\Test\Static\Fixtures\User;

/**
 * @psalm-import-type ProjectShape from Project
 * @psalm-import-type IntersectionWithOtherShape from IntersectionWithOther
 * @psalm-import-type IntersectionWithOtherPickShape from IntersectionWithOtherPick
 * @psalm-import-type IntersectionWithOtherOmitShape from IntersectionWithOtherOmit
 * @psalm-import-type ShapeWithOtherShape from ShapeWithOther
 * @psalm-import-type NewShapeByOtherShape from NewShapeByOther
 */
final class InferShapeTest extends PsalmTest
{
    /**
     * @return ProjectShape
     */
    private static function projectShape(): array
    {
        NoCode::here();
    }

    /**
     * @return IntersectionWithOtherShape
     */
    private static function intersectionWithOtherShape(): array
    {
        NoCode::here();
    }

    /**
     * @return IntersectionWithOtherPickShape
     */
    private static function intersectionWithOtherPickShape(): array
    {
        NoCode::here();
    }

    /**
     * @return IntersectionWithOtherOmitShape
     */
    private static function intersectionWithOtherOmitShape(): array
    {
        NoCode::here();
    }

    /**
     * @return ShapeWithOtherShape
     */
    private static function shapeWithOtherShape(): array
    {
        NoCode::here();
    }

    /**
     * @return NewShapeByOtherShape
     */
    private static function newShapeByOtherShape(): array
    {
        NoCode::here();
    }

    public function __invoke(): void
    {
        StaticTestCase::describe('Prop types will be inferred')
            ->haveCode(function(User $u) {
                return $u->name;
            })
            ->seeReturnType(t::string());

        StaticTestCase::describe('Nested object prop')
            ->haveCode(function(User $u) {
                return $u->projects[0]->name ?? null;
            })
            ->seeReturnType(
                t::union([t::string(), t::null()])
            );

        StaticTestCase::describe('Possibly undefined to nullable')
            ->haveCode(function(Project $p) {
                return $p->description;
            })
            ->seeReturnType(
                t::union([t::string(), t::null()])
            );

        StaticTestCase::describe('Props method inference')
            ->haveCode(function() {
                return User::shape();
            })
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape([
                        'name' => t::string(),
                        'age' => t::int(),
                        'projects' => t::list(t::object(Project::class)),
                    ]),
                ]),
            );

        StaticTestCase::describe('Generated shape type alias')
            ->haveCode(function() {
                return self::projectShape();
            })
            ->seeReturnType(
                t::shape([
                    'id' => t::int(),
                    'name' => t::string(),
                    'description' => t::string()->optional(),
                ]),
            );

        StaticTestCase::describe('Infer recursive property (by fqn)')
            ->haveCode(function(RecByFqn $department) {
                return $department->subDepartments;
            })
            ->seeReturnType(
                t::list(t::object(RecByFqn::class))
            );

        StaticTestCase::describe('Infer recursive property (by self keyword)')
            ->haveCode(function(RecBySelf $department) {
                return $department->subDepartments;
            })
            ->seeReturnType(
                t::list(t::object(RecBySelf::class))
            );

        StaticTestCase::describe('Infer arbitrary static call (self keyword return type)')
            ->haveCode(function(ShapeWithValueObject $shape) {
                return $shape->withSelf;
            })
            ->seeReturnType(t::object(SomeValueObjectWithSelfKeyword::class));

        StaticTestCase::describe('Infer arbitrary static call (type hint return type)')
            ->haveCode(function(ShapeWithValueObject $shape) {
                return $shape->withTypeHint;
            })
            ->seeReturnType(t::object(SomeValueObjectWithTypeHint::class));

        StaticTestCase::describe('Intersection with other shape (new property)')
            ->haveCode(function(IntersectionWithOther $shape) {
                return $shape->test;
            })
            ->seeReturnType(t::string());

        StaticTestCase::describe('Intersection with other shape (property from intersection)')
            ->haveCode(function(IntersectionWithOther $shape) {
                return $shape->name;
            })
            ->seeReturnType(t::string());

        StaticTestCase::describe('Intersection with other shape (type alias)')
            ->haveCode(function() {
                return self::intersectionWithOtherShape();
            })
            ->seeReturnType(
                t::shape([
                    'id' => t::int(),
                    'name' => t::string(),
                    'description' => t::string()->optional(),
                    'test' => t::string(),
                ])
            );

        StaticTestCase::describe('Intersection with other shape (pick. new property)')
            ->haveCode(function(IntersectionWithOtherPick $shape) {
                return $shape->test;
            })
            ->seeReturnType(t::string());

        StaticTestCase::describe('Intersection with other shape (pick. property from intersection)')
            ->haveCode(function(IntersectionWithOtherPick $shape) {
                return $shape->id;
            })
            ->seeReturnType(t::int());

        StaticTestCase::describe('Intersection with other shape (pick. type alias)')
            ->haveCode(function() {
                return self::intersectionWithOtherPickShape();
            })
            ->seeReturnType(
                t::shape([
                    'id' => t::int(),
                    'name' => t::string(),
                    'test' => t::string(),
                ])
            );

        StaticTestCase::describe('Intersection with other shape (omit. new property)')
            ->haveCode(function(IntersectionWithOtherOmit $shape) {
                return $shape->test;
            })
            ->seeReturnType(t::string());

        StaticTestCase::describe('Intersection with other shape (omit. property from intersection)')
            ->haveCode(function(IntersectionWithOtherOmit $shape) {
                return $shape->description;
            })
            ->seeReturnType(
                t::union([t::string(), t::null()])
            );

        StaticTestCase::describe('Intersection with other shape (omit. type alias)')
            ->haveCode(function() {
                return self::intersectionWithOtherOmitShape();
            })
            ->seeReturnType(
                t::shape([
                    'description' => t::string()->optional(),
                    'test' => t::string(),
                ])
            );

        StaticTestCase::describe('Include shape fragment (new property)')
            ->haveCode(function(ShapeWithOther $shape) {
                return $shape->test;
            })
            ->seeReturnType(t::string());

        StaticTestCase::describe('Include shape fragment (from fragment)')
            ->haveCode(function(ShapeWithOther $shape) {
                return $shape->project['id'];
            })
            ->seeReturnType(t::int());

        StaticTestCase::describe('Include shape fragment (type alias)')
            ->haveCode(function() {
                return self::shapeWithOtherShape();
            })
            ->seeReturnType(
                t::shape([
                    'project' => t::shape([
                        'id' => t::int(),
                        'name' => t::string(),
                        'description' => t::string()->optional(),
                    ]),
                    'userOrProject' => t::union([
                        t::object(Project::class),
                        t::object(User::class),
                    ]),
                    'test' => t::string(),
                ])
            );

        StaticTestCase::describe('New shape by other shape (property fetch)')
            ->haveCode(function(NewShapeByOther $shape) {
                return $shape->id;
            })
            ->seeReturnType(t::int());

        StaticTestCase::describe('New shape by other shape (type alias)')
            ->haveCode(function() {
                return self::newShapeByOtherShape();
            })
            ->seeReturnType(
                t::shape([
                    'id' => t::int(),
                    'name' => t::string(),
                ])
            );

        StaticTestCase::describe('New shape by other shape (shape call)')
            ->haveCode(function() {
                return NewShapeByOther::shape();
            })
            ->seeReturnType(
                t::generic(ShapeDecoder::class, [
                    t::shape([
                        'id' => t::int(),
                        'name' => t::string(),
                    ]),
                ]),
            );
    }
}
