<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger\Owner;

use Klimick\Decode\Decoder\UnionRuntimeData;

/**
 * @psalm-immutable
 */
final class Owner extends UnionRuntimeData
{
    protected static function cases(): array
    {
        return [
            'bot' => Bot::type(),
            'customer' => Customer::type(),
        ];
    }
}
