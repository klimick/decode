<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\UnionRuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Messenger\Messenger;

final class UnexhaustiveMatchTest
{
    public function test(): void
    {
        $messenger = Messenger::of([
            'no_args' => null,
        ]);

        /** @psalm-suppress UnexhaustiveMatchIssue */
        $_matched = $messenger->match(
            smpp: fn() => 'is smpp sms',
            telegram: fn() => 'is telegram',
        );
    }
}
