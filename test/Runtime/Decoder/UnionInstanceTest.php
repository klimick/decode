<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use Klimick\Decode\Test\Static\Fixtures\Project;
use Klimick\Decode\Test\Static\Fixtures\TaggedUserOrProject;
use Klimick\Decode\Test\Static\Fixtures\User;
use Klimick\Decode\Test\Static\Fixtures\UserOrProject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\tryCast;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

final class UnionInstanceTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name(UserOrProject::class, UserOrProject::type());
        Assert::name(TaggedUserOrProject::class, TaggedUserOrProject::type());
        Assert::name(User::class . ' | ' . Project::class, UserOrProject::union());
        Assert::name(User::class . ' | ' . Project::class, TaggedUserOrProject::union());
    }

    public function testDecodeFailed(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.name'),
                new UndefinedErrorReport('$.age'),
                new UndefinedErrorReport('$.projects'),
                new UndefinedErrorReport('$.id'),
            ]),
            actual: decode([], UserOrProject::type()),
        );
    }

    public function testDecodeFailedWhenNoDiscriminatorField(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.type'),
            ]),
            actual: decode([], TaggedUserOrProject::type()),
        );
    }

    public function testDecodeFailedWithSpecificErrorsForGivenDiscriminator(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.name'),
                new UndefinedErrorReport('$.age'),
                new UndefinedErrorReport('$.projects'),
            ]),
            actual: decode(['type' => 'user'], TaggedUserOrProject::type()),
        );
    }

    public function testDecodeSuccess(): void
    {
        $project = tryCast([
            'id' => 42,
            'name' => 'test',
            'description' => 'For test',
        ], UserOrProject::type());

        assertTrue($project->is(Project::class));
        assertEquals([
            'id' => 42,
            'name' => 'test',
            'description' => 'For test',
        ], [
            'id' => $project->value->id,
            'name' => $project->value->name,
            'description' => $project->value->description,
        ]);

        $user = tryCast([
            'name' => 'test',
            'age' => 42,
            'projects' => [],
        ], UserOrProject::type());

        assertTrue($user->is(User::class));
        assertEquals([
            'name' => 'test',
            'age' => 42,
            'projects' => [],
        ], [
            'name' => $user->value->name,
            'age' => $user->value->age,
            'projects' => $user->value->projects,
        ]);
    }

    public function testDecodeTaggedSuccess(): void
    {
        $project = tryCast([
            'type' => 'project',
            'id' => 42,
            'name' => 'test',
            'description' => 'For test',
        ], TaggedUserOrProject::type());

        assertTrue($project->is(Project::class));
        assertEquals([
            'id' => 42,
            'name' => 'test',
            'description' => 'For test',
        ], [
            'id' => $project->value->id,
            'name' => $project->value->name,
            'description' => $project->value->description,
        ]);

        $user = tryCast([
            'type' => 'user',
            'name' => 'test',
            'age' => 42,
            'projects' => [],
        ], TaggedUserOrProject::type());

        assertTrue($user->is(User::class));
        assertEquals([
            'name' => 'test',
            'age' => 42,
            'projects' => [],
        ], [
            'name' => $user->value->name,
            'age' => $user->value->age,
            'projects' => $user->value->projects,
        ]);
    }

    public function testMatch(): void
    {
        $project = tryCast([
            'id' => 42,
            'name' => 'project',
            'description' => 'For test',
        ], UserOrProject::type());

        assertEquals('project', $project->match(
            fn(User $u) => $u->name,
            fn(Project $p) => $p->name,
        ));

        $user = tryCast([
            'name' => 'user',
            'age' => 42,
            'projects' => [],
        ], UserOrProject::type());

        assertEquals('user', $user->match(
            fn(User $u) => $u->name,
            fn(Project $p) => $p->name,
        ));
    }

    public function testMatchWithTagged(): void
    {
        $project = tryCast([
            'type' => 'project',
            'id' => 42,
            'name' => 'project',
            'description' => 'For test',
        ], TaggedUserOrProject::type());

        assertEquals('project', $project->match(
            fn(User $u) => $u->name,
            fn(Project $p) => $p->name,
        ));

        $user = tryCast([
            'type' => 'user',
            'name' => 'user',
            'age' => 42,
            'projects' => [],
        ], TaggedUserOrProject::type());

        assertEquals('user', $user->match(
            fn(User $u) => $u->name,
            fn(Project $p) => $p->name,
        ));
    }

    public function testUndefinedMethodCall(): void
    {
        $project = tryCast([
            'type' => 'project',
            'id' => 42,
            'name' => 'project',
            'description' => 'For test',
        ], TaggedUserOrProject::type());

        $this->expectException(RuntimeException::class);

        /** @psalm-suppress UndefinedMagicMethod */
        $project->foo();
    }

    public function testUndefinedPropertyFetch(): void
    {
        $project = tryCast([
            'type' => 'project',
            'id' => 42,
            'name' => 'project',
            'description' => 'For test',
        ], TaggedUserOrProject::type());

        $this->expectException(RuntimeException::class);

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $_ = $project->foo;
    }
}
