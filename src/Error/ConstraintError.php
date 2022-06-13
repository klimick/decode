<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

use Klimick\Decode\Constraint\ConstraintInterface;

/**
 * @implements ErrorInterface<ConstraintInterface>
 * @psalm-immutable
 */
final class ConstraintError implements ErrorInterface
{
    /**
     * @param Context<ConstraintInterface> $context
     */
    public function __construct(
        public Context $context,
    ) {}

    public function context(): Context
    {
        return $this->context;
    }
}
