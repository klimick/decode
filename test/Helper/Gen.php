<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Eris\Generator;

final class Gen
{
    public static function int(): Generator
    {
        return new Generator\IntegerGenerator();
    }

    public static function positiveInt(): Generator
    {
        return new Generator\SuchThatGenerator(
            filter: fn(int $value) => $value > 0,
            generator: self::int(),
        );
    }

    public static function float(): Generator
    {
        return new Generator\FloatGenerator();
    }

    public static function numeric(): Generator
    {
        return self::oneOf(self::int(), self::float(), self::numericString());
    }

    public static function numericString(): Generator
    {
        return new Generator\MapGenerator(
            map: fn(int|float $number) => (string) $number,
            generator: self::oneOf(self::int(), self::float()),
        );
    }

    public static function bool(): Generator
    {
        return new Generator\BooleanGenerator();
    }

    public static function string(): Generator
    {
        return new Generator\StringGenerator();
    }

    public static function nonEmptyString(): Generator
    {
        return new Generator\SuchThatGenerator(
            filter: fn(string $value) => $value !== '',
            generator: self::string(),
        );
    }

    /**
     * @psalm-param 'int'|'positive-int'|'string'|'non-empty-string'|null $type
     * @return Generator
     */
    public static function arrKey(string $type = null): Generator
    {
        $arrKeyGen = self::oneOf(
            self::string(),
            self::nonEmptyString(),
            self::int(),
            self::positiveInt(),
        );

        $mapped = new Generator\MapGenerator(
            function(mixed $value) {
                if (is_string($value) && is_numeric($value)) {
                    return "_{$value}";
                }

                return $value;
            },
            $arrKeyGen,
        );

        return $type !== null
            ? new Generator\SuchThatGenerator(
                fn(mixed $v) => match ($type) {
                    'int' => is_int($v),
                    'positive-int' => is_int($v) && $v > 0,
                    'string' => is_string($v),
                    'non-empty-string' => is_string($v) && '' !== $v,
                },
                $mapped
            )
            : $mapped;
    }

    /**
     * @no-named-arguments
     */
    public static function oneOf(Generator $head, Generator ...$tail): Generator
    {
        return new Generator\OneOfGenerator([$head, ...$tail]);
    }

    public static function arrList(Generator $generator): Generator
    {
        return new Generator\SequenceGenerator($generator);
    }

    public static function nonEmptyArrList(Generator $generator): Generator
    {
        return new Generator\SuchThatGenerator(
            filter: fn(array $list) => !empty($list),
            generator: self::arrList($generator),
        );
    }

    public static function elements(mixed ...$items): Generator
    {
        /** @var Generator */
        return Generator\ElementsGenerator::fromArray($items);
    }

    public static function nonEmptyArr(Generator $keyGenerator, Generator $itemGenerator): Generator
    {
        return new Generator\SuchThatGenerator(
            filter: fn(array $assoc) => !empty($assoc),
            generator: self::arr($keyGenerator, $itemGenerator),
        );
    }

    public static function arr(Generator $arrayKeyG, Generator $arrayItemG): Generator
    {
        return new Generator\MapGenerator(
            function(array $seq) {
                $assoc = [];

                /** @var array{array-key, mixed} $tuple */
                foreach ($seq as $tuple) {
                    /** @var mixed $v */
                    [$k, $v] = $tuple;

                    /** @psalm-suppress MixedAssignment */
                    $assoc[$k] = $v;
                }

                return $assoc;
            },
            Gen::arrList(
                new Generator\TupleGenerator([$arrayKeyG, $arrayItemG])
            ),
        );
    }

    public static function scalarSeq(): Generator
    {
        return self::arrList(
            self::oneOf(
                self::scalar(),
                self::int(),
                self::bool(),
                self::string(),
                self::numeric(),
                self::nonEmptyString(),
                self::positiveInt(),
                self::float(),
            )
        );
    }

    public static function scalar(): Generator
    {
        return self::oneOf(
            self::int(),
            self::bool(),
            self::string(),
            self::numeric(),
            self::nonEmptyString(),
            self::positiveInt(),
            self::float(),
        );
    }

    public static function mixed(): Generator
    {
        $gens = [
            self::int(),
            self::bool(),
            self::string(),
            self::numeric(),
            self::nonEmptyString(),
            self::positiveInt(),
            self::float(),
            self::scalar(),
            self::scalarSeq(),
        ];

        return self::oneOf(
            self::arr(self::arrKey(), self::oneOf(...$gens)),
            self::nonEmptyArr(self::arrKey(), self::oneOf(...$gens)),
            self::arrList(self::oneOf(...$gens)),
            self::nonEmptyArrList(self::oneOf(...$gens)),
            ...$gens,
        );
    }
}
