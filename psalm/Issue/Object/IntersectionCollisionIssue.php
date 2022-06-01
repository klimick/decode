<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use function Fp\Collection\map;

final class IntersectionCollisionIssue extends CodeIssue
{
    /**
     * @param non-empty-list<string> $properties
     */
    public function __construct(array $properties, CodeLocation $code_location)
    {
        $duplicate = implode(', ', map($properties, fn($p) => sprintf('"%s"', $p)));

        $message = count($properties) > 1
            ? "Intersection collision: properties {$duplicate} defined more than once."
            : "Intersection collision: property {$duplicate} defined more than once.";

        parent::__construct(
            message: $message,
            code_location: $code_location,
        );
    }
}
