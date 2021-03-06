<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;
use function Klimick\Decode\Utils\getTypename;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ConstantlyDecoder extends AbstractDecoder
{
    /**
     * @param T $constant
     */
    public function __construct(public mixed $constant) { }

    public function name(): string
    {
        return 'constant<' . getTypename($this->constant) . '>';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($this->constant);
    }
}
