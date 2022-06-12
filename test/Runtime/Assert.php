<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Error\DecodeErrorInterface;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\Decode\Report\ErrorReport;
use function PHPUnit\Framework\assertEquals;

final class Assert
{
    /**
     * @param ErrorReport $expectedReport
     * @param Either<non-empty-list<DecodeErrorInterface>, mixed> $actualDecoded
     */
    public static function decodeFailed(ErrorReport $expectedReport, Either $actualDecoded): void
    {
        $actualReport = $actualDecoded->isLeft()
            ? DefaultReporter::report($actualDecoded->get())
            : new ErrorReport();

        assertEquals($expectedReport, $actualReport);
    }

    /**
     * @template T
     *
     * @param T $expectedValue
     * @param Either<non-empty-list<DecodeErrorInterface>, T> $actualDecoded
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
