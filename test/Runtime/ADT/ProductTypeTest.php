<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\ADT;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Bot;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function Klimick\Decode\Decoder\cast;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

final class ProductTypeTest extends TestCase
{
    public function testCreateSumTypeWithPositionalArgs(): void
    {
        $bot = new Bot('token-val', 'v3');

        assertEquals('token-val', $bot->token);
        assertEquals('v3', $bot->apiVersion);
    }

    public function testCreateSumTypeWithNamedArgs(): void
    {
        $bot = new Bot(token: 'token-val', apiVersion: 'v3');

        assertEquals('token-val', $bot->token);
        assertEquals('v3', $bot->apiVersion);
    }

    public function testCreateSumTypeWithPositionalAndNamedArgs(): void
    {
        $bot = new Bot('token-val', apiVersion: 'v3');

        assertEquals('token-val', $bot->token);
        assertEquals('v3', $bot->apiVersion);
    }

    public function testCreateFromUntrustedData(): void
    {
        $untrusted = ['token' => 'token-val', 'apiVersion' => 'v3'];
        $decoded = cast($untrusted, Bot::type())->get();

        assertNotNull($decoded);
        assertEquals('token-val', $decoded->token);
        assertEquals('v3', $decoded->apiVersion);
    }

    public function testJsonSerialize(): void
    {
        $bot = new Bot(
            token: '...',
            apiVersion: 'v3',
        );

        $serialized = json_encode($bot);

        self::assertEquals('{"token":"...","apiVersion":"v3"}', $serialized);
    }

    public function testUndefinedPropertyFetch(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Property 'nonexistent' is undefined. Check psalm issues.");

        $bot = new Bot('token-val', apiVersion: 'v3');

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $_ = $bot->nonexistent;
    }

    public function testTypeError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid data');

        /** @psalm-suppress InvalidArgument */
        new Bot('token-val', apiVersion: 'v4');
    }
}
