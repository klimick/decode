<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use function Fp\Collection\map;

final class RequiredObjectPropertyMissingIssue extends CodeIssue
{
    /**
     * @param list<string> $missing_properties
     */
    public function __construct(array $missing_properties, CodeLocation $code_location)
    {
        $names = implode(', ', map($missing_properties, fn(string $p) => sprintf('"%s"', $p)));

        parent::__construct(
            message: "Required decoders for properties missed: {$names}",
            code_location: $code_location,
        );
    }
}
