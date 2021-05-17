<?php

declare(strict_types=1);

namespace Klimick\Decode;

/**
 * @psalm-immutable
 */
final class TypeError
{
    public function __construct(
        public Context $context,
        public array $payload = [],
    ) { }
}
