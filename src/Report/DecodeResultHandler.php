<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\Valid;

final class DecodeResultHandler
{
    /**
     * @template T
     *
     * @param Either<Invalid, Valid<T>> $value
     * @return ErrorReport|T
     */
    public static function handle(Either $value, bool $useShortClassNames = false): mixed
    {
        return $value->fold(
            ifRight: fn(Valid $v): mixed => $v->value,
            ifLeft: fn(Invalid $v): ErrorReport => DefaultReporter::report($v, $useShortClassNames)
        );
    }
}
