<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use JsonSerializable;
use Klimick\Decode\Internal\ConstantDecoder;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

/**
 * @extends Runtype<UnionDecoder<SumType&static>>
 * @psalm-immutable
 */
abstract class SumType extends Runtype implements JsonSerializable
{
    private string $caseId;
    private ProductType|SumType $instance;

    final public function __construct(ProductType|SumType $case)
    {
        $this->caseId = static::getCaseIdByInstance($case);
        $this->instance = $case;
    }

    private static function getCaseIdByInstance(ProductType|SumType $instance): string
    {
        foreach (static::definition()->cases as $type => $decoder) {
            if ($decoder->is($instance)) {
                return $type;
            }
        }

        throw new RuntimeException('Unable to create SumType. Check psalm issues.');
    }

    final public function match(callable ...$matchers): mixed
    {
        /** @psalm-suppress ImpureFunctionCall */
        return ($matchers[$this->caseId])($this->instance);
    }

    /**
     * @psalm-return UnionDecoder<static>
     */
    final public static function type(): UnionDecoder
    {
        $constructor = static function(array $properties): static {
            $classReflection = new ReflectionClass(static::class);

            /** @var static $sumType */
            $sumType = $classReflection->newInstanceWithoutConstructor();

            /** @var mixed $instance */
            $instance = $properties['instance'] ?? new RuntimeException();

            /** @var mixed $caseId */
            $caseId = $properties['caseId'] ?? new RuntimeException();

            $instanceReflection = new ReflectionProperty(SumType::class, 'instance');
            $instanceReflection->setAccessible(true);
            $instanceReflection->setValue($sumType, $instance);
            $instanceReflection->setAccessible(false);

            $caseIdReflection = new ReflectionProperty(SumType::class, 'caseId');
            $caseIdReflection->setAccessible(true);
            $caseIdReflection->setValue($sumType, $caseId);
            $caseIdReflection->setAccessible(false);

            return $sumType;
        };

        $sumCases = static::definition();

        $decoders = array_map(
            fn($decoder, $case) => new ObjectDecoder(
                objectClass: static::class,
                decoders: [
                    'instance' => $decoder->from('$'),
                    'caseId' => new ConstantDecoder($case),
                ],
                customConstructor: $constructor
            ),
            array_values($sumCases->cases),
            array_keys($sumCases->cases),
        );

        return new UnionDecoder($decoders);
    }

    public function jsonSerialize(): ProductType|SumType
    {
        return $this->instance;
    }

    abstract protected static function definition(): SumCases;
}
