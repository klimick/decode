<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Klimick\Decode\Error\ErrorInterface;

/**
 * @psalm-immutable
 */
final class Invalid
{
    /**
     * @param non-empty-list<ErrorInterface> $errors
     */
    public function __construct(
        public array $errors,
    ) { }
}
