<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\RuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Message;

final class UndefinedPropertyFetchTest
{
    public function test(): void
    {
        $message = Message::of([
            'id' => '...',
            'senderId' => '...',
            'receiverId' => '...',
        ]);

        /** @psalm-suppress UndefinedPropertyFetchIssue */
        $_ = $message->misspelledReceiverId;
    }
}
