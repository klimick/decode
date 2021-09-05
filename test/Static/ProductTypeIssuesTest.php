<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Test\Static\Fixtures\Message;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;

final class ProductTypeIssuesTest extends PsalmTest
{
    public function __invoke(): void
    {
        StaticTestCase::describe('Undefined property fetch issue')
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

        StaticTestCase::describe('Type error issue')
            ->haveCode(function() {
                return new Message(
                    id: '...',
                    senderId: '...',
                    receiverId: 123456,
                );
            })
            ->seePsalmIssue(
                type: 'InvalidProductTypeInstantiationIssue',
                message: 'Invalid type for "receiverId". Actual: "123456". Expected: "string".',
            );

        StaticTestCase::describe('Less arguments issue')
            ->haveCode(function() {
                return new Message(
                    id: '...',
                    senderId: '...',
                );
            })
            ->seePsalmIssue(
                type: 'InvalidProductTypeInstantiationIssue',
                message: 'Expected args 3. Actual count 2.',
            );
    }
}
