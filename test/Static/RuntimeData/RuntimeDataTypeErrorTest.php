<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\RuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Message;

final class RuntimeDataTypeErrorTest
{
    /**
     * @psalm-suppress RuntimeDataTypeErrorIssue
     */
    public function test(): void
    {
        Message::of([
            'id' => '...',
            'senderId' => '...',
            'receiverId' => 123456,
        ]);
    }
}
