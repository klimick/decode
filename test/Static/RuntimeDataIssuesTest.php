<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use DateTimeImmutable;
use Klimick\Decode\Test\Static\Fixtures\Message;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;

final class RuntimeDataIssuesTest extends PsalmTest
{
    public function testUndefinedPropertyFetchIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function(): mixed {
                $message = Message::of([
                    'id' => '...',
                    'senderId' => '...',
                    'receiverId' => '...',
                ]);

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
                return Message::of([
                    'id' => '...',
                    'senderId' => '...',
                    'receiverId' => 123456,
                ]);
            })
            ->seePsalmIssue(
                type: 'RuntimeDataTypeErrorIssue',
                message: 'Wrong value at $.receiverId. Expected type: string. Actual type: int.',
            );
    }

    public function testRuntimeDataPropertyMissingIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                return Message::of([
                    'id' => '...',
                    'senderId' => '...',
                ]);
            })
            ->seePsalmIssue(
                type: 'RuntimeDataPropertyMissingIssue',
                message: 'Required property "receiverId" at path $ is missing.',
            );
    }
    public function testCouldNotAnalyzeOfCallIssue(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                /** @psalm-var DateTimeImmutable $id */
                $id = '...';

                return Message::of([
                    'id' => $id,
                    'senderId' => '...',
                    'receiverId' => '...',
                ]);
            })
            ->seePsalmIssue(
                type: 'CouldNotAnalyzeOfCallIssue',
                message: 'RuntimeData::of call could not be analyzed because array value is not literal.'
            );
    }
}
