<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Error\DecodeError;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\Decode\Report\ErrorReport;
use function PHPUnit\Framework\assertEquals;

final class Assert
{
    /**
     * @param ErrorReport $expected
     * @param Either<non-empty-list<DecodeError>, mixed> $actual
     */
    public static function decodeFailed(ErrorReport $expected, Either $actual): void
    {
        $actualReport = $actual->isLeft()
            ? DefaultReporter::report($actual->get())
            : new ErrorReport();

        assertEquals($expected, $actualReport);
    }

    /**
     * @template T
     *
     * @param T $expectedValue
     * @param Either<non-empty-list<DecodeError>, T> $actualDecoded
     */
    public static function decodeSuccess(mixed $expectedValue, Either $actualDecoded): void
    {
        $case = $actualDecoded->isRight() ? 'Right' : 'Left';
        assertEquals($expectedValue, $actualDecoded->get(), "actualDecoded should be Right, but actually {$case}");
    }

    /**
     * @param non-empty-string $expected
     */
    public static function name(string $expected, DecoderInterface $actual): void
    {
        assertEquals($expected, $actual->name(), 'Expected decoder name and actual should be equals');
    }
}
