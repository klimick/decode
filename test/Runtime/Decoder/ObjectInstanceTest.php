<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use Klimick\Decode\Test\Static\Fixtures\Project;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\tryCast;
use function PHPUnit\Framework\assertEquals;

final class ObjectInstanceTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name(Project::class, Project::type());
        Assert::name('array{id: int, name: string, description?: string}', Project::shape());
    }

    public function testDecodeFailed(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.id'),
                new UndefinedErrorReport('$.name'),
            ]),
            actual: decode([], Project::type()),
        );
    }

    public function testDecodeSuccess(): void
    {
        $project = tryCast([
            'id' => 42,
            'name' => 'test',
            'description' => 'For test',
        ], Project::type());

        assertEquals(42, $project->id);
        assertEquals('test', $project->name);
        assertEquals('For test', $project->description);
    }
}
