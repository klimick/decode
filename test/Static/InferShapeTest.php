<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Fp\PsalmToolkit\StaticTest\NoCode;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\PartialProject;
use Klimick\Decode\Test\Static\Fixtures\Project;
use Klimick\Decode\Test\Static\Fixtures\User;
use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use Fp\PsalmToolkit\StaticType\StaticTypes as t;

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

        StaticTestCase::describe('Possibly undefined to nullable (partial shape)')
            ->haveCode(function(PartialProject $p) {
                return $p->id;
            })
            ->seeReturnType(
                t::union([t::int(), t::null()])
            );

        $user = t::shape([
            'name' => t::string(),
            'age' => t::int(),
            'projects' => t::list(t::object(Project::class)),
        ]);

        StaticTestCase::describe('Props method inference')
            ->haveCode(function() {
                return User::shape();
            })
            ->seeReturnType(
                t::intersection([
                    t::generic(DecoderInterface::class, [$user]),
                    t::generic(ShapeDecoder::class, [$user]),
                ])
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
    }
}
