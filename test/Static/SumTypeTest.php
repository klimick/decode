<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Messenger;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Bot;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Owner;
use Klimick\Decode\Test\Static\Fixtures\Messenger\SmppSms;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Whatsapp;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;
use Klimick\PsalmTest\StaticType\StaticTypes as t;

final class SumTypeTest extends PsalmTest
{
    private static function messenger(): Messenger
    {
        $bot = new Bot(
            token: '...',
            apiVersion: 'v3',
        );

        $telegram = new Telegram(
            telegramId: 'test-id',
            owner: new Owner(case: $bot)
        );

        return new Messenger(case: $telegram);
    }

    public function testMatchTypeInference(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                return self::messenger()->match(
                    smpp: fn(SmppSms $_m) => 1,
                    telegram: fn(Telegram $_m) => 2,
                    whatsapp: fn(Whatsapp $_m) => 3,
                );
            })
            ->seeReturnType(
                t::union([
                    t::literal(1), t::literal(2), t::literal(3)
                ])
            );
    }

    public function testUnexhaustiveMatchIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                return self::messenger()->match(
                    smpp: fn() => 'is smpp sms',
                    telegram: fn() => 'is telegram',
                );
            })
            ->seePsalmIssue(
                type: 'UnexhaustiveMatchIssue',
                message: 'Match with name "whatsapp" is not specified',
            );
    }

    public function testInvalidMatcherTypeIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                return self::messenger()->match(
                    smpp: fn(SmppSms $m) => get_debug_type($m),
                    telegram: fn(Whatsapp $m) => get_debug_type($m),
                    whatsapp: fn(Telegram $m) => get_debug_type($m),
                );
            })
            ->seePsalmIssue(
                type: 'InvalidMatcherTypeIssue',
                message: 'Invalid matcher type given. Expected type: #[telegram]. Actual type: pure-Closure(#[whatsapp]):string.',
                args: [
                    'telegram' => Telegram::class,
                    'whatsapp' => Whatsapp::class,
                ],
            )
            ->seePsalmIssue(
                type: 'InvalidMatcherTypeIssue',
                message: 'Invalid matcher type given. Expected type: #[whatsapp]. Actual type: pure-Closure(#[telegram]):string.',
                args: [
                    'telegram' => Telegram::class,
                    'whatsapp' => Whatsapp::class,
                ],
            );
    }
}
