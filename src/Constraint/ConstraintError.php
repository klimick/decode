<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;

/**
 * @psalm-immutable
 */
final class ConstraintError
{
    /**
     * @param non-empty-string $constraint
     */
    public function __construct(
        public Context $context,
        public string $constraint,
        public array $payload,
    ) { }
}
