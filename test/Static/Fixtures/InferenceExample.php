<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Telegram;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\fromJson;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\intersection;
use function Klimick\Decode\Decoder\literal;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\partialObject;
use function Klimick\Decode\Decoder\partialShape;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tuple;
use function Klimick\Decode\Decoder\union;

final class InferenceExample extends ProductType
{
    protected static function definition(): ShapeDecoder
    {
        return shape(
            atomic: int(),
            union: union(null(), string()),
            literal: literal('manager', 'worker', 'bot'),
            object: object(Person::class)(
                name: string(),
                age: int(),
            ),
            partialObject: partialObject(PartialPerson::class)(
                name: string(),
                age: int(),
            ),
            shape: shape(
                city: string(),
                postcode: int(),
            ),
            shapeWithOptional: shape(
                city: string(),
                postcode: int()->optional(),
            ),
            partialShape: partialShape(
                city: string(),
                postcode: int(),
            ),
            tuple: tuple(string(), int()),
            intersection: intersection(
                shape(foo: string()),
                shape(bar: bool()),
            ),
            runtype: Telegram::type(),
            rec: rec(fn() => InferenceExample::type()),
            recWithSelf: rec(fn() => self::type()),
            fromJson: fromJson(
                shape(foo: int())
            ),
        );
    }
}
