<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\RuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Message;

final class CouldNotAnalyseOfCallIssue
{
    public function test(): void
    {
        /** @var string $id */
        $id = '...';

        /** @var non-empty-string $senderId */
        $senderId = '...';

        /** @var string $receiverId */
        $receiverId = '...';

        /** @psalm-suppress CouldNotAnalyzeOfCallIssue */
        $_ = Message::of([
            'id' => $id,
            'senderId' => $senderId,
            'receiverId' => $receiverId,
        ]);
    }
}
