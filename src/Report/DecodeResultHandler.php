<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\DecodeErrorInterface;

final class DecodeResultHandler
{
    /**
     * @template T
     *
     * @param Either<non-empty-list<DecodeErrorInterface>, T> $value
     * @return ErrorReport|T
     */
    public static function handle(Either $value, bool $useShortClassNames = false): mixed
    {
        return $value
            ->mapLeft(fn(array $v): ErrorReport => DefaultReporter::report($v, $useShortClassNames))
            ->get();
    }
}
