<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Closure;
use DateTimeZone;
use Exception;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\Invalid;
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
        public string $timezone,
        public null|string $fromFormat,
    ) { }

    public function name(): string
    {
        return DateTimeImmutable::class;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return string()
            ->decode($value, $context)
            ->map(fn($datetime) => $datetime->value)
            ->flatMap(null !== $this->fromFormat
                ? self::createFromFormat($context, new DateTimeZone($this->timezone), $this->fromFormat)
                : self::createWithConstructor($context, new DateTimeZone($this->timezone))
            );
    }

    /**
     * @return Closure(string): Either<Invalid, Valid<DateTimeImmutable>>
     * @psalm-pure
     */
    private static function createWithConstructor(Context $context, DateTimeZone $timezone): Closure
    {
        return function($datetimeString) use ($context, $timezone) {
            try {
                $datetime = new DateTimeImmutable($datetimeString, $timezone);
            } catch (Exception) {
                return invalid($context);
            }

            return valid($datetime);
        };
    }

    /**
     * @return Closure(string): Either<Invalid, Valid<DateTimeImmutable>>
     * @psalm-pure
     */
    private static function createFromFormat(Context $context, DateTimeZone $timezone, string $format): Closure
    {
        return function($datetimeString) use ($context, $timezone, $format) {
            $datetime = DateTimeImmutable::createFromFormat($format, $datetimeString, $timezone);

            if (false === $datetime) {
                return invalid($context);
            }

            return valid($datetime);
        };
    }
}
