<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class IntersectionCollisionIssue extends CodeIssue
{
    /**
     * @param non-empty-list<string> $properties
     */
    public function __construct(array $properties, CodeLocation $code_location)
    {
        $duplicate = implode(', ', array_map(fn($p) => sprintf('"%s"', $p), $properties));

        $message = count($properties) > 1
            ? "Intersection collision: properties {$duplicate} defined more than once."
            : "Intersection collision: property {$duplicate} defined more than once.";

        parent::__construct(
            message: $message,
            code_location: $code_location,
        );
    }
}
