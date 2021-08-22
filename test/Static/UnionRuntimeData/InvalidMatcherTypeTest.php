<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\UnionRuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Messenger;
use Klimick\Decode\Test\Static\Fixtures\Messenger\SmppSms;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Whatsapp;

final class InvalidMatcherTypeTest
{
    public function test(): void
    {
        $messenger = Messenger::of([
            'no_args' => null,
        ]);

        /** @psalm-suppress InvalidMatcherTypeIssue */
        $_matched = $messenger->match(
            smpp: fn(SmppSms $m) => get_debug_type($m),
            telegram: fn(Whatsapp $m) => get_debug_type($m),
            whatsapp: fn(Telegram $m) => get_debug_type($m),
        );
    }
}
