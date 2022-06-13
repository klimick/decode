<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;

/**
 * @template TFor of DecoderInterface|ConstraintInterface
 * @psalm-immutable
 */
final class ContextEntry
{
    /**
     * @param TFor $instance
     */
    public function __construct(
        public DecoderInterface|ConstraintInterface $instance,
        public mixed $actual,
        public string $key,
    ) {}
}
