<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class RequiredObjectPropertyMissingIssue extends CodeIssue
{
    public function __construct(array $missing_properties, CodeLocation $code_location)
    {
        $names = implode(', ', array_map(fn(string $p) => sprintf('"%s"', $p), $missing_properties));

        parent::__construct(
            message: "Required decoders for properties missed: {$names}",
            code_location: $code_location,
        );
    }
}
