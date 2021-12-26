<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Test\Static\Fixtures\InferenceExample;
use Klimick\Decode\Test\Static\Fixtures\Message;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use Klimick\Decode\Test\Static\Fixtures\PartialPerson;
use Klimick\Decode\Test\Static\Fixtures\Person;
use Klimick\PsalmTest\NoCode;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;
use Klimick\PsalmTest\StaticType\StaticTypes as t;

final class ProductTypeIssuesTest extends PsalmTest
{
    public static function runtype(): InferenceExample
    {
        NoCode::here();
    }

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

        StaticTestCase::describe('Atomic inference')
            ->haveCode(function() {
                return self::runtype()->atomic;
            })
            ->seeReturnType(t::int());

        StaticTestCase::describe('Union inference')
            ->haveCode(function() {
                return self::runtype()->union;
            })
            ->seeReturnType(t::union(
                [t::null(), t::string()]
            ));

        StaticTestCase::describe('Literal inference')
            ->haveCode(function() {
                return self::runtype()->literal;
            })
            ->seeReturnType(t::union([
                t::literal('manager'),
                t::literal('worker'),
                t::literal('bot'),
            ]));

        StaticTestCase::describe('Object inference')
            ->haveCode(function() {
                return self::runtype()->object;
            })
            ->seeReturnType(t::object(Person::class));

        StaticTestCase::describe('Partial object inference')
            ->haveCode(function() {
                return self::runtype()->partialObject;
            })
            ->seeReturnType(t::object(PartialPerson::class));

        StaticTestCase::describe('Shape inference')
            ->haveCode(function() {
                return self::runtype()->shape;
            })
            ->seeReturnType(t::shape([
                'city' => t::string(),
                'postcode' => t::int(),
            ]));

        StaticTestCase::describe('Shape with optional inference')
            ->haveCode(function() {
                return self::runtype()->shapeWithOptional;
            })
            ->seeReturnType(t::shape([
                'city' => t::string(),
                'postcode' => t::int()->optional(),
            ]));

        StaticTestCase::describe('Partial shape inference')
            ->haveCode(function() {
                return self::runtype()->partialShape;
            })
            ->seeReturnType(t::shape([
                'city' => t::string()->optional(),
                'postcode' => t::int()->optional(),
            ]));

        StaticTestCase::describe('Tuple inference')
            ->haveCode(function() {
                return self::runtype()->tuple;
            })
            ->seeReturnType(t::shape([
                t::string(),
                t::int(),
            ]));

        StaticTestCase::describe('Intersection inference')
            ->haveCode(function() {
                return self::runtype()->intersection;
            })
            ->seeReturnType(t::shape([
                'foo' => t::string(),
                'bar' => t::bool(),
            ]));

        StaticTestCase::describe('Runtype inference')
            ->haveCode(function() {
                return self::runtype()->runtype;
            })
            ->seeReturnType(t::object(Telegram::class));

        StaticTestCase::describe('Rec inference')
            ->haveCode(function() {
                return self::runtype()->rec;
            })
            ->seeReturnType(t::object(InferenceExample::class));

        StaticTestCase::describe('Rec with self inference')
            ->haveCode(function() {
                return self::runtype()->recWithSelf;
            })
            ->seeReturnType(t::object(InferenceExample::class));

        StaticTestCase::describe('From json inference')
            ->haveCode(function() {
                return self::runtype()->fromJson;
            })
            ->seeReturnType(t::shape([
                'foo' => t::int(),
            ]));
    }
}
