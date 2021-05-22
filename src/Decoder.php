<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Fp\Functional\Either\Either;

/**
 * @template-covariant T
 * @psalm-immutable
 */
abstract class Decoder
{
    /**
     * @return non-empty-string
     */
    abstract public function name(): string;

    /**
     * @return Either<Invalid, Valid<T>>
     */
    abstract public function decode(mixed $value, Context $context): Either;
}
