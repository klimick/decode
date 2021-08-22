<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger;

use Klimick\Decode\Decoder\UnionRuntimeData;

/**
 * @psalm-immutable
 */
final class Messenger extends UnionRuntimeData
{
    protected static function cases(): array
    {
        return [
            'smpp' => SmppSms::type(),
            'telegram' => Telegram::type(),
            'whatsapp' => Whatsapp::type(),
        ];
    }
}
