<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Messenger;
use Klimick\Decode\Test\Static\Fixtures\Messenger\SmppSms;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Whatsapp;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Owner;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;
use Klimick\PsalmTest\StaticType\StaticTypes as t;

final class UnionRuntimeDataTest extends PsalmTest
{
    public function testMatchTypeInference(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                $messenger = Messenger::of([
                    'no_args' => null,
                ]);

                return $messenger->match(
                    smpp: fn(SmppSms $m) => ['smpp', $m, $m->owner],
                    telegram: fn(Telegram $m) => ['telegram', $m, $m->owner],
                    whatsapp: fn(Whatsapp $m) => ['whatsapp', $m, $m->owner],
                );
            })
            ->seeReturnType(
                t::shape([
                    t::union(
                        t::literal('smpp'),
                        t::literal('telegram'),
                        t::literal('whatsapp'),
                    ),
                    t::union(
                        t::object(SmppSms::class),
                        t::object(Telegram::class),
                        t::object(Whatsapp::class),
                    ),
                    t::object(Owner::class),
                ])
            );
    }

    public function testUnexhaustiveMatchIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                $messenger = Messenger::of([
                    'no_args' => null,
                ]);

                return $messenger->match(
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
                $messenger = Messenger::of([
                    'no_args' => null,
                ]);

                return $messenger->match(
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
