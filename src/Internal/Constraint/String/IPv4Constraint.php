<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class IPv4Constraint implements ConstraintInterface
{
    private const IP_V4_REGEX = '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/';
    private const MAX_OCTET = 255;

    public function name(): string
    {
        return 'IP_V4';
    }

    public function check(Context $context, mixed $value): Either
    {
        if (1 === preg_match(self::IP_V4_REGEX, $value, $m)) {
            $octet1 = (int) $m[0];
            $octet2 = (int) $m[1];
            $octet3 = (int) $m[2];
            $octet4 = (int) $m[3];

            $isValid =
                ($octet1 <= self::MAX_OCTET) &&
                ($octet2 <= self::MAX_OCTET) &&
                ($octet3 <= self::MAX_OCTET) &&
                ($octet4 <= self::MAX_OCTET);

            if ($isValid) {
                return valid();
            }
        }

        return invalid($context, $this, ['message' => 'non ipv4 string']);
    }
}
