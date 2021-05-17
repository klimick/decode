<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Fp\Functional\Either\Either;

/**
 * @template-covariant T
 * @psalm-immutable
 */
interface DecoderInterface
{
    /**
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * @return Either<Invalid, Valid<T>>
     */
    public function decode(mixed $value, Context $context): Either;
}
