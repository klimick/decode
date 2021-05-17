<?php

declare(strict_types=1);

namespace Klimick\Decode;

/**
 * @template TDecoded
 * @psalm-immutable
 */
final class Valid
{
    /**
     * @param TDecoded $value
     */
    public function __construct(public mixed $value) { }
}
