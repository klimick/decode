<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger\Owner;

use Klimick\Decode\Decoder\SumType;

/**
 * @psalm-immutable
 */
final class Owner extends SumType
{
    protected static function definition(): array
    {
        return [
            'bot' => Bot::type(),
            'customer' => Customer::type(),
        ];
    }
}
