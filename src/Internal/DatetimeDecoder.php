<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use DateTimeZone;
use Exception;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\valid;

/**
 * @psalm-immutable
 * @extends AbstractDecoder<DateTimeImmutable>
 */
final class DatetimeDecoder extends AbstractDecoder
{
    public function __construct(
        public string $timezone = 'UTC',
    ) { }

    public function name(): string
    {
        return DateTimeImmutable::class;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return string()
            ->decode($value, $context)
            ->flatMap(function($datetimeString) use ($context) {
                try {
                    $timezone = new DateTimeZone($this->timezone);
                    $datetime = new DateTimeImmutable($datetimeString->value, $timezone);
                } catch (Exception) {
                    return invalid($context);
                }

                return valid($datetime);
            });
    }
}
