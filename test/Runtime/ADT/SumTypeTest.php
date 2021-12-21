<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\ADT;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Bot;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Customer;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Owner;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function Klimick\Decode\Decoder\cast;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

final class SumTypeTest extends TestCase
{
    public function testCreate(): void
    {
        $owner = new Owner(
            case: new Bot(
                token: '...',
                apiVersion: 'v3',
            )
        );

        assertEquals('bot', $owner->match(
            customer: fn(Customer $_v) => 'customer',
            bot: fn(Bot $_v) => 'bot',
        ));
    }

    public function testCreateFromUntrustedData(): void
    {
        $untrusted = ['token' => 'token-val', 'apiVersion' => 'v3'];
        $decoded = cast($untrusted, Owner::type())->get();

        assertNotNull($decoded);
        assertEquals('bot', $decoded->match(
            customer: fn(Customer $_v) => 'customer',
            bot: fn(Bot $_v) => 'bot',
        ));
    }

    public function testJsonSerialize(): void
    {
        $owner = new Owner(
            case: new Bot(
                token: '...',
                apiVersion: 'v3',
            )
        );

        $serialized = json_encode($owner);

        self::assertEquals('{"token":"...","apiVersion":"v3"}', $serialized);
    }

    public function testTypeError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to create SumType. Check psalm issues.');

        $invalidCase = new Telegram(
            telegramId: '...',
            owner: new Owner(
                case: new Bot(token: '...', apiVersion: 'v3'),
            ),
        );

        /** @psalm-suppress InvalidArgument */
        new Owner(case: $invalidCase);
    }
}
