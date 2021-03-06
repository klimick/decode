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
    public static function decodeFailed(ErrorReport $expected, Either $actual, bool $useShortClassName = false): void
    {
        $actualReport = $actual->isLeft()
            ? DefaultReporter::report($actual->get(), $useShortClassName)->toString()
            : 'No errors!';

        assertEquals($expected->toString(), $actualReport);
    }

    /**
     * @template T
     *
     * @param T $expected
     * @param Either<non-empty-list<DecodeError>, T> $actual
     */
    public static function decodeSuccess(mixed $expected, Either $actual): void
    {
        assertEquals($expected, $actual
            ->mapLeft(fn($errors) => DefaultReporter::report($errors)->toString())
            ->get());
    }

    /**
     * @param non-empty-string $expected
     */
    public static function name(string $expected, DecoderInterface $actual): void
    {
        assertEquals($expected, $actual->name(), 'Expected decoder name and actual should be equals');
    }
}
