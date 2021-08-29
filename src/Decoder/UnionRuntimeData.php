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
 * @psalm-immutable
 */
abstract class UnionRuntimeData implements JsonSerializable
{
    private string $caseId;
    private RuntimeData|UnionRuntimeData $instance;

    final public function __construct(RuntimeData|UnionRuntimeData $case)
    {
        $this->caseId = static::getCaseIdByInstance($case);
        $this->instance = $case;
    }

    private static function getCaseIdByInstance(RuntimeData|UnionRuntimeData $instance): string
    {
        foreach (static::cases() as $type => $decoder) {
            if ($decoder->is($instance)) {
                return $type;
            }
        }

        throw new RuntimeException('Unable to create UnionRuntimeData. Check psalm issues.');
    }

    final public function match(callable ...$matchers): mixed
    {
        return ($matchers[$this->caseId])($this->instance);
    }

    /**
     * @psalm-return AbstractDecoder<static> & UnionDecoder<static>
     */
    final public static function type(): AbstractDecoder
    {
        $constructor = static function(array $properties): static {
            $classReflection = new ReflectionClass(static::class);

            /** @var static $instance */
            $instance = $classReflection->newInstanceWithoutConstructor();

            $instanceReflection = new ReflectionProperty(UnionRuntimeData::class, 'instance');
            $instanceReflection->setAccessible(true);
            $instanceReflection->setValue($instance, $properties['instance']);
            $instanceReflection->setAccessible(false);

            $caseIdReflection = new ReflectionProperty(UnionRuntimeData::class, 'caseId');
            $caseIdReflection->setAccessible(true);
            $caseIdReflection->setValue($instance, $properties['caseId']);
            $caseIdReflection->setAccessible(false);

            return $instance;
        };

        $cases = static::cases();

        return new UnionDecoder(
            ...array_map(
                fn($decoder, $case) => new ObjectDecoder(
                    objectClass: static::class,
                    decoders: [
                        'instance' => $decoder->from('$'),
                        'caseId' => new ConstantDecoder($case)
                    ],
                    customConstructor: $constructor
                ),
                array_values($cases),
                array_keys($cases),
            )
        );
    }

    public function jsonSerialize(): RuntimeData|UnionRuntimeData
    {
        return $this->instance;
    }

    /**
     * @psalm-return non-empty-array<non-empty-string, ObjectDecoder<RuntimeData> | UnionDecoder<UnionRuntimeData>>
     */
    abstract protected static function cases(): array;
}
