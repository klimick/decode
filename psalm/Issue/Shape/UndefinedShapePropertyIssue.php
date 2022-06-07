<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Shape;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type\Atomic\TKeyedArray;
use function implode;

final class UndefinedShapePropertyIssue extends CodeIssue
{
    /**
     * @param non-empty-list<string> $undefined_props
     */
    public function __construct(TKeyedArray $shape, array $undefined_props, CodeLocation $code_location)
    {
        $props = implode(', ', $undefined_props);

        parent::__construct(
            message: count($undefined_props) === 1
                ? "Property {$props} is not defined on shape {$shape->getId()}"
                : "Properties {$props} are not defined on shape {$shape->getId()}",
            code_location: $code_location,
        );
    }
}
