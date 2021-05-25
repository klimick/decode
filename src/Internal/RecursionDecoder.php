<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\AbstractDecoder;
use Klimick\Decode\Context;

/**
 * @template T of object
 * @psalm-immutable
 * @extends AbstractDecoder<T>
 */
final class RecursionDecoder extends AbstractDecoder
{
    /**
     * @var null|AbstractDecoder<T>
     * @psalm-allow-private-mutation
     */
    private ?AbstractDecoder $cache = null;

    /**
     * @param Closure(): AbstractDecoder<T> $type
     */
    public function __construct(public Closure $type) {}

    /**
     * @return AbstractDecoder<T>
     */
    private function type(): AbstractDecoder
    {
        if (null === $this->cache) {
            $this->cache = ($this->type)();
        }

        /** @var AbstractDecoder<T> */
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
