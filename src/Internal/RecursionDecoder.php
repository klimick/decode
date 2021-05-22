<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder;
use Klimick\Decode\Context;

/**
 * @template T of object
 * @psalm-immutable
 * @extends Decoder<T>
 */
final class RecursionDecoder extends Decoder
{
    /**
     * @var null|Decoder<T>
     * @psalm-allow-private-mutation
     */
    private ?Decoder $cache = null;

    /**
     * @param Closure(): Decoder<T> $type
     */
    public function __construct(public Closure $type) {}

    /**
     * @return Decoder<T>
     */
    private function type(): Decoder
    {
        if (null === $this->cache) {
            $this->cache = ($this->type)();
        }

        /** @var Decoder<T> */
        return $this->cache;
    }

    public function name(): string
    {
        return $this->type()->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->type()->decode($value, $context);
    }
}
