<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Test\Static\Fixtures\Message;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;

final class RuntimeDataIssuesTest extends PsalmTest
{
    public function testUndefinedPropertyFetchIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function(): mixed {
                /** @var Message $message */
                $message = null;

                return $message->misspelledReceiverId;
            })
            ->seePsalmIssue(
                type: 'UndefinedPropertyFetchIssue',
                message: 'Property "misspelledReceiverId" is not present in "#[instance]" instance.',
                args: [
                    'instance' => Message::class,
                ],
            );
    }

    public function testRuntimeDataTypeErrorIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                return new Message(
                    id: '...',
                    senderId: '...',
                    receiverId: 123456,
                );
            })
            ->seePsalmIssue(
                type: 'UnsafeRuntimeDataInstantiation',
                message: 'Invalid type for "receiverId". Actual: "123456". Expected: "string".',
            );
    }

    public function testRuntimeDataPropertyMissingIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                return new Message(
                    id: '...',
                    senderId: '...',
                );
            })
            ->seePsalmIssue(
                type: 'UnsafeRuntimeDataInstantiation',
                message: 'Expected args 3. Actual count 2.',
            );
    }
}
