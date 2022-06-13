<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;

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
            ->flatMap(null !== $this->fromFormat
                ? self::createFromFormat($context, new DateTimeZone($this->timezone), $this->fromFormat)
                : self::createWithConstructor($context, new DateTimeZone($this->timezone))
            );
    }

    /**
     * @param Context<DecoderInterface> $context
     * @return Closure(string): Either<non-empty-list<DecodeError>, DateTimeImmutable>
     *
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
     * @param Context<DecoderInterface> $context
     * @return Closure(string): Either<non-empty-list<DecodeError>, DateTimeImmutable>
     *
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
