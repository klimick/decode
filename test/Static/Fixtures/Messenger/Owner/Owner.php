<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger\Owner;

use Klimick\Decode\Decoder\SumType;
use Klimick\Decode\Decoder\SumCases;
use function Klimick\Decode\Decoder\cases;
use function Klimick\Decode\Decoder\sumType;

/**
 * @psalm-immutable
 */
final class Owner extends SumType
{
    protected static function definition(): SumCases
    {
        return cases(
            bot: sumType(Bot::class),
            customer: sumType(Customer::class),
        );
    }
}
