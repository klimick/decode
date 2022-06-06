<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Fp\PsalmToolkit\StaticTest\NoCode;
use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use Fp\PsalmToolkit\StaticType\StaticTypes as t;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\UnionDecoder;
use Klimick\Decode\Test\Static\Fixtures\Project;
use Klimick\Decode\Test\Static\Fixtures\User;
use Klimick\Decode\Test\Static\Fixtures\UserOrProject;
use RuntimeException;

/**
 * @psalm-import-type UserOrProjectUnion from UserOrProject
 */
final class InferUnionTest extends PsalmTest
{
    /**
     * @return UserOrProjectUnion
     */
    public static function getUnion(): User|Project
    {
        NoCode::here();
    }

    public function __invoke(): void
    {
        StaticTestCase::describe('Property value is User|Project')
            ->haveCode(function(UserOrProject $union) {
                return $union->value;
            })
            ->seeReturnType(
                t::union([
                    t::object(User::class),
                    t::object(Project::class),
                ]),
            );

        StaticTestCase::describe('Value type can be narrowed')
            ->haveCode(function(UserOrProject $union) {
                if ($union->is(User::class)) {
                    return $union->value;
                }

                throw new RuntimeException('never');
            })
            ->seeReturnType(t::object(User::class));

        StaticTestCase::describe('Value can be matched (No type hints)')
            ->haveCode(function(UserOrProject $union) {
                return $union->match(
                    fn($user) => [$user, 'user'],
                    fn($project) => [$project, 'project'],
                );
            })
            ->seeReturnType(
                t::union([
                    t::shape([t::object(User::class), t::literal('user')]),
                    t::shape([t::object(Project::class), t::literal('project')]),
                ])
            );

        StaticTestCase::describe('Value can be matched (With type hints)')
            ->haveCode(function(UserOrProject $union) {
                return $union->match(
                    fn(User $user) => [$user, 'user'],
                    fn(Project $project) => [$project, 'project'],
                );
            })
            ->seeReturnType(
                t::union([
                    t::shape([t::object(User::class), t::literal('user')]),
                    t::shape([t::object(Project::class), t::literal('project')]),
                ])
            );

        StaticTestCase::describe('InferUnion::union infers correctly')
            ->haveCode(function() {
                return UserOrProject::union();
            })
            ->seeReturnType(
                t::intersection([
                    t::generic(DecoderInterface::class, [
                        t::union([t::object(User::class), t::object(Project::class)]),
                    ]),
                    t::generic(UnionDecoder::class, [
                        t::union([t::object(User::class), t::object(Project::class)]),
                    ]),
                ])
            );

        StaticTestCase::describe('InferUnion::type infers correctly')
            ->haveCode(function() {
                return UserOrProject::type();
            })
            ->seeReturnType(
                t::generic(DecoderInterface::class, [
                    t::object(UserOrProject::class)
                ])
            );

        StaticTestCase::describe('InferUnion have magic type alias')
            ->haveCode(function() {
                return self::getUnion();
            })
            ->seeReturnType(
                t::union([
                    t::object(User::class),
                    t::object(Project::class),
                ]),
            );
    }
}
