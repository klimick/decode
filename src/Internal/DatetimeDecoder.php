<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use DateTimeZone;
use Exception;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\string;
use function Klimick\Decode\valid;

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
            ->flatMap(function(Valid $maybeDatetime) use ($context) {
                try {
                    $timezone = new DateTimeZone($this->timezone);
                    $datetime = new DateTimeImmutable($maybeDatetime->value, $timezone);

                    return valid($datetime);
                } catch (Exception) {
                    return invalid($context);
                }
            });
    }
}
