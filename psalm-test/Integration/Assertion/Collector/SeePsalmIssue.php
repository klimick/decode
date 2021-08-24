<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

/**
 * @psalm-immutable
 */
final class SeePsalmIssue
{
    public function __construct(
        public string $type,
        public string $message,
    ) {}
}
