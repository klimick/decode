<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Error;

use Klimick\Decode\Constraint\ConstraintError;

/**
 * @psalm-immutable
 */
final class ConstraintsError implements DecodeErrorInterface
{
    /**
     * @param non-empty-list<ConstraintError> $errors
     */
    public function __construct(public array $errors) { }
}
