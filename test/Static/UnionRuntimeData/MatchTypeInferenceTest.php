<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\UnionRuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Messenger;
use Klimick\Decode\Test\Static\Fixtures\Messenger\SmppSms;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Whatsapp;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Owner;

/**
 * @psalm-type MessengerTypes = 'smpp'|'telegram'|'whatsapp'
 */
final class MatchTypeInferenceTest
{
    public function test(): void
    {
        $messenger = Messenger::of([
            'no_args' => null,
        ]);

        $matched = $messenger->match(
            smpp: fn(SmppSms $m) => ['smpp', $m, $m->owner],
            telegram: fn(Telegram $m) => ['telegram', $m, $m->owner],
            whatsapp: fn(Whatsapp $m) => ['whatsapp', $m, $m->owner],
        );

        self::testAssertMatchType($matched);
    }

    /**
     * @param array{MessengerTypes, SmppSms|Telegram|Whatsapp, Owner} $_param
     */
    private static function testAssertMatchType(array $_param): void
    {
    }
}
