<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Fp\PsalmToolkit\StaticTest\NoCode;
use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use Fp\PsalmToolkit\StaticType\StaticTypes as t;
use Klimick\Decode\Decoder\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Project;
use Klimick\Decode\Test\Static\Fixtures\RecByFqn;
use Klimick\Decode\Test\Static\Fixtures\RecBySelf;
use Klimick\Decode\Test\Static\Fixtures\ShapeWithValueObject;
use Klimick\Decode\Test\Static\Fixtures\SomeValueObjectWithSelfKeyword;
use Klimick\Decode\Test\Static\Fixtures\SomeValueObjectWithTypeHint;
use Klimick\Decode\Test\Static\Fixtures\User;

/**
 * @psalm-import-type ProjectShape from Project
 */
final class InferShapeTest extends PsalmTest
{
    /**
     * @return ProjectShape
     */
    private static function getProjectShape(): array
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
                return self::getProjectShape();
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
    }
}
