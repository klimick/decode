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
                type: 'UndefinedMagicPropertyFetch',
                message: 'Magic instance property #[instance]::$misspelledReceiverId is not defined',
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
                type: 'InvalidScalarArgument',
                message: 'Argument 3 of #[message]::__construct expects string, 123456 provided',
                args: [
                    'message' => Message::class,
                ],
            );

        StaticTestCase::describe('Less arguments issue')
            ->haveCode(function() {
                return new Message(
                    id: '...',
                    senderId: '...',
                );
            })
            ->seePsalmIssue(
                type: 'TooFewArguments',
                message: 'Too few arguments for #[message]::__construct - expecting 3 but saw 2',
                args: [
                    'message' => Message::class,
                ],
            );
    }
}
