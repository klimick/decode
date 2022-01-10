<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Eris\Generator;
use Klimick\Decode\Decoder as d;

/**
 * @psalm-type SimpleDecoders = value-of<self::SIMPLE_DECODERS>
 */
final class DecoderGenerator
{
    public const DECODERS = [
        'int',
        'positive-int',
        'float',
        'numeric',
        'numeric-string',
        'string',
        'non-empty-string',
        'bool',
        'scalar',
        'mixed',
        'list',
        'non-empty-list',
        'array',
        'non-empty-array',
        'shape',
        'tuple',
    ];

    public const SIMPLE_DECODERS = [
        'int',
        'positive-int',
        'float',
        'numeric',
        'numeric-string',
        'string',
        'non-empty-string',
        'bool',
        'scalar',
        'mixed',
    ];

    /**
     * @param SimpleDecoders $name
     */
    private static function simpleDecoderByName(string $name): d\DecoderInterface
    {
        return match ($name) {
            'int' => d\int(),
            'positive-int' => d\positiveInt(),
            'float' => d\float(),
            'numeric' => d\numeric(),
            'numeric-string' => d\numericString(),
            'string' => d\string(),
            'non-empty-string' => d\nonEmptyString(),
            'bool' => d\bool(),
            'scalar' => d\scalar(),
            'mixed' => d\mixed(),
        };
    }

    /**
     * @param SimpleDecoders $name
     */
    private static function simpleGenByName(string $name): Generator
    {
        return match ($name) {
            'int' => Gen::int(),
            'positive-int' => Gen::positiveInt(),
            'float' => Gen::float(),
            'numeric' => Gen::numeric(),
            'numeric-string' => Gen::numericString(),
            'string' => Gen::string(),
            'non-empty-string' => Gen::nonEmptyString(),
            'bool' => Gen::bool(),
            'scalar' => Gen::scalar(),
            'mixed' => Gen::mixed(),
        };
    }

    /**
     * @psalm-return array{d\DecoderInterface<mixed>, Generator}
     */
    public static function generate(int $level = 1): array
    {
        $decoderName = $level < 2
            ? self::DECODERS[array_rand(self::DECODERS)]
            : self::SIMPLE_DECODERS[array_rand(self::SIMPLE_DECODERS)];

        if ('shape' === $decoderName) {
            $keys = [
                'prop1',
                'prop2',
                'prop3',
                'prop4',
            ];

            /** @var list<string> $keysK */
            $keysK = array_rand(
                array: $keys,
                num: random_int(2, count($keys) - 1),
            );

            $shapeDecoder = [];
            $shapeGenerator = [];

            for ($i = 0; $i < count($keysK); $i++) {
                [$decoder, $generator] = DecoderGenerator::generate();

                $shapeDecoder[$keys[$i]] = $decoder;
                $shapeGenerator[$keys[$i]] = $generator;
            }

            return [
                d\shape(...$shapeDecoder),
                new Generator\AssociativeArrayGenerator($shapeGenerator),
            ];
        }

        if ('list' === $decoderName || 'non-empty-list' === $decoderName) {
            [$listItemD, $listItemG] = self::generate($level + 1);

            return $decoderName === 'non-empty-list'
                ? [d\nonEmptyListOf($listItemD), Gen::nonEmptyArrList($listItemG)]
                : [d\listOf($listItemD), Gen::arrList($listItemG)];
        }

        if ('array' === $decoderName || 'non-empty-array' === $decoderName) {
            $arrayKeys = [
                'int',
                'positive-int',
                'string',
                'non-empty-string',
            ];

            $arrayKey = $arrayKeys[array_rand($arrayKeys)];

            /** @var d\DecoderInterface<array-key> $arrayKeyD */
            $arrayKeyD = self::simpleDecoderByName($arrayKey);
            $arrayKeyG = Gen::arrKey($arrayKey);

            [$arrayItemD, $arrayItemG] = self::generate($level + 1);

            return 'non-empty-array' === $decoderName
                ? [d\nonEmptyArr($arrayKeyD, $arrayItemD), Gen::nonEmptyArr($arrayKeyG, $arrayItemG)]
                : [d\arr($arrayKeyD, $arrayItemD), Gen::arr($arrayKeyG, $arrayItemG)];
        }

        if ('tuple' === $decoderName) {
            $tupleLength = random_int(1, 5);

            $tupleDecoders = [];
            $tupleGenerators = [];

            for ($i = 0; $i < $tupleLength; $i++) {
                [$decoder, $generator] = DecoderGenerator::generate();

                $tupleDecoders[] = $decoder;
                $tupleGenerators[] = $generator;
            }

            return [
                d\tuple(...$tupleDecoders),
                new Generator\AssociativeArrayGenerator($tupleGenerators),
            ];
        }

        return [
            self::simpleDecoderByName($decoderName),
            self::simpleGenByName($decoderName),
        ];
    }
}
