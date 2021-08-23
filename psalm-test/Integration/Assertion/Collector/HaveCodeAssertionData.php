<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Klimick\PsalmTest\Integration\Assertion\AssertionData;
use Psalm\CodeLocation;
use Psalm\Type;

/**
 * @psalm-immutable
 */
final class HaveCodeAssertionData implements AssertionData
{
    public function __construct(
        public CodeLocation $code_location,
        public Type\Union $actual_return_type,
    ) {}
}
