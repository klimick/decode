<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

/**
 * @psalm-immutable
 */
final class Invalid
{
    /**
     * @param non-empty-list<DecodeErrorInterface> $errors
     */
    public function __construct(
        public array $errors,
    ) { }

    public function isUndefined(): bool
    {
        return 1 === count($this->errors) && $this->errors[0] instanceof UndefinedError;
    }
}
