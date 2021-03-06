<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @template T of object
 * @psalm-immutable
 * @extends AbstractDecoder<T>
 */
final class RecursionDecoder extends AbstractDecoder
{
    /**
     * @var null|DecoderInterface<T>
     * @psalm-allow-private-mutation
     */
    private ?DecoderInterface $cache = null;

    /**
     * @psalm-param pure-Closure(): DecoderInterface<T> $type
     */
    public function __construct(private Closure $type) {}

    /**
     * @return DecoderInterface<T>
     */
    private function type(): DecoderInterface
    {
        if (null === $this->cache) {
            $this->cache = ($this->type)();
        }

        /** @var DecoderInterface<T> */
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
