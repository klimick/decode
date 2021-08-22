<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\RuntimeData;

use Klimick\Decode\Test\Static\Fixtures\Message;

final class RuntimeDataPropertyMissingTest
{
    /**
     * @psalm-suppress RuntimeDataPropertyMissingIssue
     */
    public function test(): void
    {
        Message::of([
            'id' => '...',
            'senderId' => '...',
        ]);
    }
}
