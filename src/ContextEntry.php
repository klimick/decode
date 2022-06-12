<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;

/**
 * @psalm-immutable
 */
final class ContextEntry
{
    public function __construct(
        public DecoderInterface|ConstraintInterface $instance,
        public mixed $actual,
        public string $key,
    ) {}
}
