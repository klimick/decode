<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint\Metadata;

/**
 * @psalm-immutable
 */
interface ConstraintMetaInterface
{
    /**
     * @return non-empty-string
     */
    public function name(): string;
}
